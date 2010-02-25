CREATE TABLE tx_tendflickr_cache (
  cache_fingertip varchar(32) NOT NULL,
  cache_time timestamp NOT NULL,
  response text NOT NULL,
  KEY cache_fingertip_p (cache_fingertip)
);