#!/usr/bin/python

import re, logging
from GmailWrapper import GmailWrapper
from php import drushPush
from datetime import datetime
SEEN_FLAG = 'SEEN'
UNSEEN_FLAG = 'UNSEEN'
HOST_NAME = 'imap.gmail.com'
USER_NAME = 'ccd.venmo'
PASS = 'fobh ulkf nhgb hvfc'
LOG_FILENAME = "CCDDrushVenmo.log"




if __name__=="__main__":
  logging.basicConfig(filename=LOG_FILENAME,level=logging.DEBUG,filemode="w+")
  logging.info("Venmo script beginning at %s"  % datetime.now().strftime("%D %H:%M:%S"))
	while True:
		GmailWrapper(drushPush,logger=logging)
		logging.info("Going on a short break. Be back in a sec!")
		time.sleep(5)
