CREATE TABLE IF NOT EXISTS grabbed_request (
   request_id BIGINT(11) NOT NULL AUTO_INCREMENT,
   request_timestamp DATETIME(6) DEFAULT CURRENT_TIMESTAMP(6) NOT NULL,
   request_method VARCHAR(16) NOT NULL,
   ip_remote_addr VARCHAR(64) NULL, 
   ip_forwarded_for VARCHAR(64) NULL,
   remote_port VARCHAR(8) NULL,
   user_agent TEXT NULL,
   PRIMARY KEY (request_id)
);

CREATE TABLE IF NOT EXISTS grabbed_content (
   content_id BIGINT(11) NOT NULL AUTO_INCREMENT,
   grabbed_content_fk BIGINT(11) REFERENCES grabbed_request(request_id),
   content_type VARCHAR(64) NOT NULL, 
   content_key TEXT NOT NULL,
   content_value TEXT NULL,
   PRIMARY KEY (content_id)
);