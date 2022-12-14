#!/usr/bin/python

# PHP code to push node updates and perform creation
import subprocess, os

#def createPHP(name, amount):
#
#	php = """<?php
#
#	/*
#	*  Calculate when this payment should be produced
#	*/
#
#	$unixDate = new DateTime();
#	if(date('D') == 'Fri') {
#	  $unixDate = strtotime('today'); // Is today Friday?
#	}
#	else {
#	  $unixDate = strtotime('next friday'); // If not, apply to following Friday
#	}
#	$node = new stdClass();  // Create a new node object
#	$node->type = 'venmo_payment';  // Content type
#	$node->language = LANGUAGE_NONE;  // Or e.g. 'en' if locale is enabled
#	node_object_prepare($node);  //Set some default values
#
#	$node->title = "Venmo Payment from %s";
#	$node->field_amount['und'][0]['value'] = "%s";
#	$node->field_dancer_name['und'][0]['value'] = "%s";
#	$node->field_payment_ts['und'][0]['value'] = time();
#	$node->field_event_date['und'][0]['value'] = date("Y-m-d", $unixDate);
#	$node->field_event_date['und'][0]['value2'] = date("Y-m-d", $unixDate);
#
#	$node->status = 1;   // (1 or 0): published or unpublished
#	$node->promote = 0;  // (1 or 0): promoted to front page or not
#	$node->sticky = 0;  // (1 or 0): sticky at top of lists or not
#	$node->comment = 1;  // 2 = comments open, 1 = comments closed, 0 = comments hidden
#
#	// Add author of the node
#	$node->uid = 1;
#
#	// Save the node
#	node_save($node);
#""" % (name, amount, name)
#	return  php

# # Drupal 7 Version:
# def createPHP(name, amount):
#
# 	php = """<?php
#
# 	/* Find the node for the next event date
#
# 	$unixDate = new DateTime();
# 	if(date('D') == 'Fri') {
# 	  $unixDate = strtotime('today'); // Is today Friday?
# 	}
# 	else {
# 	  $unixDate = strtotime('next friday'); // If not, apply to following Friday
# 	}
# 	$query = new EntityFieldQuery();
#
# 	$query->entityCondition('entity_type', 'node')
# 	->entityCondition('bundle', 'event')
# 	->propertyCondition('status', 1)
# 	->fieldCondition('field_event_type', 'tid', 4, '=')
# 	->fieldCondition('field_event_date', 'value', array(date("Y-m-d 00:00:00", $unixDate), date("Y-m-d 23:59:59", $unixDate)), 'BETWEEN')
# 	->addMetaData('account', user_load(1));
#
# 	$result=$query->execute();
# 	if (isset($result['node'])) {
# 	  $venmo_pay_nid = array_keys($result['node'][0]);
# 	/*  $venmo_node = entity_load('node', $venmo_pay_nid);
# 	  $instance = [
# 	    'field_name' => 'field_venmo_payment',
# 	    'bundle' => 'event,
# 	    'entity_type' => 'node',
# 	  ];
# 	  $venmo_node->field_venmo_payment[] = [
# 	    LANGUAGE_NONE => [
# 	      0 => [
# 	        'field_venmo_name' => [
# 	          LANGUAGE_NONE => [
# 	            0 => [
# 	              'value' => '%s',
# 	            ],
# 	          ],
# 	        ],
# 	        'field_venmo_amount' => [
# 	          LANGUAGE_NONE => [
# 	            0 => [
# 	              'value' => %s,
# 	            ],
# 	          ],
# 	        ],
# 	        'field_timestamp' => [
# 	          LANGUAGE_NONE => [
# 	            0 => [
# 	              'value' => time(),
# 	            ],
# 	          ],
# 	        ],
# 	        'field_event_date' => [
# 	          LANGUAGE_NONE => [
# 	            0 => [
# 	              'value1' => date("Y-m-d 00:00:00", $unixDate),
# 	              'value2' => date("Y-m-d 00:00:00", $unixDate),
# 	            ],
# 	          ],
# 	        ],
# 	        'field_memo' => [
# 	          LANGUAGE_NONE => [
# 	            0 => [
# 	              'value' => "Automatically generated",
# 	            ],
# 	          ],
# 	        ],
# 	      ],
# 	    ],
# 	  ];
# 	$venmo_node->save();
# 	}
#
# 	""" % (name, amount, name)
# 	return php

# Drupal 8 Version:
def createPHP(name, amount):

	php = """<?php

use Drupal\\node\Entity\Node;
use Drupal\paragraphs\entity\Paragraph;

/* Find the node for the next event date */
$tz = new \DateTimeZone("UTC");
$local_tz = new \DateTimeZone(drupal_get_user_timezone());
$now = new \DateTime('now', $tz);
$now_local = new \DateTime('now', $local_tz);

$query = \Drupal::entityQuery('node')
 ->condition('type','event')
 ->condition('status',1)
 ->condition('field_event_type', 2, '=')
 ->condition('field_event_date.end_value', $now->format("Y-m-d\TH:i:s"),'>')
 ->sort('field_event_date.end_value','ASC')
 ->range(0,1);
$result=$query->execute();

if (isset(array_keys($result)[0])) {
  $venmo_pay_nid = $result[array_keys($result)[0]];
  $node=\Drupal\\node\Entity\Node::load($venmo_pay_nid);

 // Create single new paragraph

  $paragraph = \Drupal\paragraphs\Entity\Paragraph::create([
    'type' => 'venmo_payment',
    'field_dancer_name' => "%s",
    'field_amount' => %s,
    'field_note' => 'Automatically generated by Venmo Python Script',
    'field_timestamp' => $now_local->format("Y-m-d\TH:i:s")
  ]);
  $paragraph->isNew();
  $paragraph->save();

  if ($node->hasField('field_venmo_payment')) {
    $current = $node->get('field_venmo_payment')->getValue();
    $current[] = array(
      'target_id' => $paragraph->id(),
     'target_revision_id' => $paragraph->getRevisionId(),
    );
  } else {
    $current = array(
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    );
  }
  $node->set('field_venmo_payment', $current);
  $node->save();
}	""" % (name, amount)
	return php

def drushPush(subs,logger=None):
	logging=logger
	if not subs:
		try:
			logging.info("Received blank submission. Skipping Drush push")
		except:
			print("Recevied blanks submission. Skipping Drush push")
		return
	for (a,b) in subs:
		print(createPHP(a,b))
		try:
			f=open("/tmp/venmo.php","w")
			f.write(createPHP(a,b))
			logging.debug("PHP File generated")
			f.close()
			try:
				sub=subprocess.Popen("runuser -l ec2-user -c 'drush -r=/var/app/current --script-path=/tmp scr venmo.php'", shell=True)
				ret=sub.communicate()
			except:
				logging.exception("Drush failed to execute")
			try:
				os.remove('/tmp/venmo.php')
				pass
			except:
				logging.warning("Unable to delete venmo.php file")
		except Exception as e:
			print("Failed to execute drush command with error: %s" % str(e))

