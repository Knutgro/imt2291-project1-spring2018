/* 
CREATED BY KNUT GRØSTAD
PART2 OF ASSIGNMENT4 */

DROP SCHEMA 

IF EXISTS VID;
	CREATE SCHEMA VID COLLATE = utf8_danish_ci;

USE VID;


create table user  (
  email			varchar(250) NOT NULL,
  password		varchar(250)  NOT NULL, 
  type    	    enum('admin','student','lecturer')  NOT NULL,
  PRIMARY KEY(email)
);

create table video (
  id      			int NOT NULL auto_increment,
  title     		varchar(20) NOT NULL,
  description		varchar(250),
  videoPath			varchar(250),
  thumbnailPath		varchar(250),
  subject			varchar(20) NOT NULL,
  theme				varchar(20) NOT NULL,
  ownerEmail		varchar(250) NOT NULL,
  primary key(id),
  foreign key(ownerEmail)
	references user(email)
);

create table rating (
  videoId	    	int NOT NULL,
  ownerEmail		varchar(250) NOT NULL,
  rating        	ENUM('0','1','2','3','4','5'),
  primary key(videoId, ownerEmail),
  foreign key(ownerEmail)
	references user(email),
  foreign key(videoId)
	references video(id)
);
  
  
create table comment (
  id      			int NOT NULL auto_increment,
  ownerEmail		varchar(250) NOT NULL,
  videoId			int NOT NULL,
  commment	    	varchar(250),
  primary key(id, ownerEmail, videoId),
  foreign key(ownerEmail)
	references user(email),
  foreign key(videoId)
	references video(id)
);

create table playlist (
  id      			int NOT NULL auto_increment,
  ownerEmail		varchar(250) NOT NULL,
  title    			varchar(20) NOT NULL,  
  description		varchar(250),
  primary key(id,ownerEmail),
  foreign key(ownerEmail)
	references user(email)
);
  
create table playlistvideos  (
  playlistId      	int NOT NULL,
  ownerEmail		varchar(250) NOT NULL,
  videoId			int NOT NULL,
  primary key(playlistId,ownerEmail,videoId),
  foreign key (playlistId)
	references playlist(id),
  foreign key (ownerEmail)
	references user(email),
  foreign key (videoId)
	references video(id)
);


create table subscription  (
  ownerEmail  	    varchar(250) NOT NULL, 
  playlistId    	int NOT NULL,
  primary key(ownerEmail,playlistId),
  foreign key(ownerEmail)
	references user(email),
  foreign key(playlistId)
	references playlist(id)
);


/* INSERTING VALUES TO DATABASE */  
INSERT INTO user
VALUES  (
	'admin','admin','admin'
);





  