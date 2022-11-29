#!/usr/bin/python

# GMail Wrapper
import imaplib2, imaplib, time, ssl, logging, re, datetime, time
from imapclient import SEEN
from threading import *
from datetime import datetime
import pytz, os

SEEN_FLAG = SEEN
UNSEEN_FLAG = 'UNSEEN'
HOST_NAME = 'imap.gmail.com'
USER_NAME = 'ccd.venmo'
PASS = os.environ.get('CCD_GMAIL_APP_PASS')

class GmailWrapper:
	def __init__(self,push,logger=None):
		# force the user to pass along username and password to log in as
		self.host = HOST_NAME
		self.userName = USER_NAME
		self.password = PASS
		if logger: logging=logger
		else:
		    import logging
		    logging.basicConfig(filename=LOG_FILENAME,level=logging.DEBUG)
		self.pushMethod=push
		logging.debug("Beginning IMAP SSL Connection Debug")
		logging.info("Beginning IMAP SSL Connection")
		self.login()

	def login(self):
		M = imaplib2.IMAP4_SSL(host=self.host, port=993)
		try:
			logging.info("Logging in as  %s " % self.userName)
			M.login(self.userName, self.password)
			logging.info("Login successful")
		except:
			logging.error("Could not log in", exc_info=True)
			raise
		M.select("INBOX")
		idler = self.Idler(M, self.getIdsBySubject, self.markAsRead, self.pushMethod, self.parseEmails)
		try:
			logging.info("Checking initial mail")
			idler.dosync()
			logging.info("Activating idler")
			idler.start()
		except Exception as e:
			logging.exception("Could not start idler. Exception occured")
			raise
		time.sleep(60*60*24)
		idler.stop()
		idler.join()
		M.close()
		M.logout()

	#   The IMAPClient search returns a list of Id's that match the given criteria.
	#   An Id in this case identifies a specific email
	def getIdsBySubject(self, conn, subject, unreadOnly=True, folder='INBOX'):
		#   search within the specified folder, e.g. Inbox
		self.seenCriteria = UNSEEN_FLAG

		if(unreadOnly == False):

			#   force the search to include "read" emails too
			self.seenCriteria = SEEN_FLAG

                #   build the search criteria (e.g. unread emails with the given subject)
		self.searchCriteria = [subject]
		#   conduct the search and return the resulting Ids
		while True:
			num=1
			try:
				string_literal = r' '.join(str(c) for c in self.searchCriteria)
				logging.debug("Approximate string ltieral passed to server: %s SUBJECT %s" % (self.seenCriteria, string_literal))
				#return conn.search("UTF-8", self.seenCriteria, u"SUBJECT", string_literal.encode("UTF-8"))[1][0]
				res =  conn.search("UTF-8",'(UNSEEN SUBJECT "paid you")')[1][0]
				return res.decode('utf-8')
			except Exception as e:
				if num==5:
					logging.exception("Attempt %s: Could not perform inbox search after 5 attempts. Stopping" % str(num))
					raise
				logging.debug("Exception %s" % e)
				logging.exception("Attempt %s: Could not perform inbox search. Waiting 60 seconds" % num)
				time.sleep(60)
				num+=1


	def markAsRead(self, mailIds, server):
		for num in mailIds.split(" "):
			if num!=" ":
				try:
					server.store(num, "+FLAGS", SEEN)
					logging.debug("Setting mailID %s to read" % str(num))
				except Exception as e:
					logging.exception("Failed to mark email %s as read. Exception occured" % str(num))

	def parseEmails(self, mailIds, server, sub):
		subs=[]

		for num in mailIds.split(" "):

			try:
				typ, data = server.fetch(num, '(RFC822.SIZE BODY[HEADER.FIELDS (SUBJECT)])')
			except Exception as e:
				logging.exception("Mail id %s failed to find subject. Exception: %s" % (num, e))
				return False
			try:
				typ, timedata = server.fetch(num, '(INTERNALDATE)')
			except Exception as e:
                                logging.exception("Mail id %s failed to find sent time. Exception: %s" % (num, e))
                                return False
			try:
				full_message = str(data[0][1], "utf-8")
				logging.debug("Full message: %s " % str(full_message))
				message = full_message.lstrip('Subject: ').strip() + ' '
				logging.debug("Subject decoded: %s " % message)
				timestruct = imaplib.Internaldate2tuple(timedata[0])
				time_delivered = time.mktime(timestruct)
				timedelivered_ts_utc = datetime.utcfromtimestamp(time_delivered)
				local_tz = pytz.timezone("US/Eastern")
				timedelivered_ts_aware_utc = timedelivered_ts_utc.replace(tzinfo=pytz.utc)
				timedelivered_ts_aware_local = timedelivered_ts_aware_utc.astimezone(local_tz)
				local_timestamp = timedelivered_ts_aware_local.timestamp()
				utc_timestamp = timedelivered_ts_aware_utc.timestamp()
				logging.debug("timestamp data: %s" % str(local_timestamp))
			except Exception as e:
				logging.exception("Failed to find subject line in message: %s" % data[0][1])
				continue
			try:
				res = [message.rsplit("paid")[0], re.findall(r'(?:[\$]{1}[,\d]+.?\d*)', message)[0][1:]]
				res.append(utc_timestamp)
			except Exception as e:
				logging.exception("Failed to parse message: %s in mail %s" % (str(message), str(num)))
				continue
			subs.append(res)
		return subs



	class Idler(object):
		def __init__(self, conn, gids, mar, push, pE):
			self.thread = Thread(target=self.idle)
			print("Is me daemon?")
			print(self.thread.isDaemon())
			self.thread.setDaemon(True)
			print("HOw about now")
			print(self.thread.isDaemon())
			self.M = conn
			self.gids = gids
			self.mar = mar
			self.push = push
			self.pe = pE
			self.event = Event()
		def start(self):
			logging.info("Beginning thread")
			self.thread.start()
		def stop(self):
			self.event.set()
		def join(self):
			self.thread.join()
		def idle(self):
			while True:
				if self.event.isSet():
					return
				self.needsync = False
				def callback(args):
					if not self.event.isSet():
						self.needsync = True
						self.event.set()
				self.M.idle(callback=callback)
				self.event.wait()
				if self.needsync:
					self.event.clear()
					self.dosync()
		def dosync(self):
			logging.debug("Event triggered")
			mailIds=self.gids(self.M, 'paid you', unreadOnly=True)
			logging.debug("Found the following mail Ids")
			logging.debug(mailIds)
			if (((len(mailIds.split(" ")) > 0) & (mailIds!=" "))):
				self.mar(mailIds, self.M)
				for mailId in mailIds.split(" "):
					logging.debug("Attempting to process mail")
					if logging: self.push(self.pe(mailId, self.M, "paid you"), logger=logging)
					else: self.push(self.pe(mailId, self.M, "paid you"))
					logging.debug("Mail processed")
