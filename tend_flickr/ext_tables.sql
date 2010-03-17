CREATE TABLE tx_tendflickr_cache (
  cache_fingertip varchar(32) DEFAULT '' NOT NULL,
  cache_time timestamp NOT NULL,
  response text NOT NULL,
  KEY cache_fingertip_p (cache_fingertip)
);

CREATE TABLE tx_tendflickr_photo (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	title varchar(255) DEFAULT '' NOT NULL,
	description text,
	author varchar(255) DEFAULT '' NOT NULL,
	photo text,
	flickr_meta varchar(255) DEFAULT '' NOT NULL,
        flickr_mail varchar(255) DEFAULT '' NOT NULL,
        from_mail varchar(255) DEFAULT '' NOT NULL,
	upload_timestamp int(11) DEFAULT '0' NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);