#!/usr/bin/python

import re, logging
from GmailWrapper import GmailWrapper
from php import drushPush

SEEN_FLAG = 'SEEN'
UNSEEN_FLAG = 'UNSEEN'
HOST_NAME = 'imap.gmail.com'
USER_NAME = 'ccd.venmo'
PASS = 'fobh ulkf nhgb hvfc'



if __name__=="__main__":
	while True:
		GmailWrapper(drushPush)
		logging.info("Going on a short break. Be back in a sec!")
		time.sleep(5)
