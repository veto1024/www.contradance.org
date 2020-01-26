#!/usr/bin/python

# GMail Wrapper
import imaplib2, imaplib, time, ssl, logging, re
from imapclient import SEEN
from threading import *

SEEN_FLAG = 'SEEN'
UNSEEN_FLAG = 'UNSEEN'
HOST_NAME = 'imap.gmail.com'
USER_NAME = 'ccd.venmo'
PASS = 'fobh ulkf nhgb hvfc'

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

		#   build the search criteria (e.g. unread emails with the given subject)
		#self.searchCriteria = [UNSEEN_FLAG, 'SUBJECT', subject]

		if(unreadOnly == False):
			#   force the search to include "read" emails too
			self.searchCriteria.append(SEEN_FLAG)

		#   conduct the search and return the resulting Ids
		while True:
			num=1
			try:
				return conn.search("UTF-8", UNSEEN_FLAG, ("SUBJECT"), u'\"%s\"' % subject)[1][0]
			except Exception as e:
				if num==5:
					logging.exception("Attempt %s: Could not perform inbox search after 5 attempts. Stopping" % str(num))
					raise
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
				message = data[0][1].lstrip('Subject: ').strip() + ' '
				logging.debug("Message decoded: %s " % message)
			except Exception as e:
				logging.exception("Failed to find subject line in message: %s" % str({data[0][1]}))
				continue
			try:
				res = [message.rsplit("paid")[0], re.findall(r'(?:[\$]{1}[,\d]+.?\d*)', message)[0][1:]]
			except Exception as e:
				logging.exception("Failed to parse message: %s in mail %s" % (str(message), str(num)))

			subs.append(res)
		return subs



	class Idler(object):
		def __init__(self, conn, gids, mar, push, pE):
			self.thread = Thread(target=self.idle)
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
			mailIds=self.gids(self.M, "paid you")
			if (((len(mailIds.split(" ")) > 0) & (mailIds!=" "))):
				self.mar(mailIds, self.M)
				for mailId in mailIds.split(" "):
					logging.debug("Attempting to process mail")
					if logging: self.push(self.pe(mailId, self.M, "paid you"), logger=logging)
					else: self.push(self.pe(mailId, self.M, "paid you"))
					logging.debug("Mail processed")
