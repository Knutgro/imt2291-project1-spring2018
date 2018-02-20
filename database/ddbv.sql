

DROP SCHEMA IF EXISTS www_proj1;
CREATE SCHEMA www_proj1 COLLATE = utf8_general_ci;

USE www_proj1;


create table user  (
    id              int NOT NULL auto_increment,

    email           varchar(255) NOT NULL UNIQUE,  -- MySQL er sær på UCer mot TEXT
    password        tinytext NOT NULL, 
    type            enum('admin','student','lecturer')  NOT NULL,
    verified        boolean DEFAULT FALSE,

    PRIMARY KEY(id)
);

create table video (
    id              int NOT NULL auto_increment,
    title           text NOT NULL,
    description     tinytext NOT NULL,
    videoPath       text NOT NULL,
    thumbnailPath   text NOT NULL,
    subject         tinytext NOT NULL,
    topic           tinytext NOT NULL,
    user            int NOT NULL,

    primary key(id),
    foreign key(user) references user(id)
);

create table rating (
    video           int NOT NULL,
    user            int NOT NULL,
    rating          ENUM('0','1','2','3','4','5') NOT NULL,

    primary key(video, user),
    foreign key(user)  references user(id),
    foreign key(video) references video(id)
);


create table comment (
    id              int NOT NULL auto_increment,
    user            int NOT NULL,
    video           int NOT NULL,
    commment        text NOT NULL,

    primary key(id),
    foreign key(user)  references user(id),
    foreign key(video) references video(id)
);

create table playlist (
    id              int NOT NULL auto_increment,
    user            int NOT NULL,
    title           tinytext NOT NULL,
    description     text NOT NULL,
    subject         tinytext NOT NULL,
    topic           tinytext NOT NULL,

    primary key(id),
    foreign key(user) references user(id)
);

create table playlistvideos  (
	no				int NOT NULL,
    playlist        int NOT NULL,
    video           int NOT NULL,

    primary key (playlist, video),
    foreign key (playlist) references playlist(id),
    foreign key (video)    references video(id)
);


create table subscription  (
    user            int NOT NULL, 
    playlist        int NOT NULL,

    primary key(user, playlist),
    foreign key(user)     references user(id),
    foreign key(playlist) references playlist(id)
);


/* INSERTING VALUES TO DATABASE */  
INSERT INTO user (email, password, type, verified)
VALUES  (
    -- Password: "do not use in production"
    'video-admin@ntnu.no', '$2y$10$7kPPWtRzSWCoAeog/WfQru0rRYQXelbklzg4kvBrcHJIeR5VQfRRe', 'admin', 1
);
