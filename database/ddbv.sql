

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
    title           tinytext NOT NULL,
    description     text NOT NULL,
    videoPath       text NOT NULL,
    thumbnailPath   text NOT NULL,
    subject         tinytext NOT NULL,
    topic           tinytext NOT NULL,
    user            int NOT NULL,

    primary key(id),
    foreign key(user) references user(id) ON DELETE CASCADE
);

create table rating (
    video           int NOT NULL,
    user            int NOT NULL,
    rating          int NOT NULL,

    primary key(video, user),
    foreign key(user)  references user(id) ON DELETE CASCADE,
    foreign key(video) references video(id) ON DELETE CASCADE
);


create table comment (
    id              int NOT NULL auto_increment,
    user            int NOT NULL,
    video           int NOT NULL,
    comment        text NOT NULL,

    primary key(id),
    foreign key(user)  references user(id) ON DELETE CASCADE,
    foreign key(video) references video(id) ON DELETE CASCADE
);

create table playlist (
    id              int NOT NULL auto_increment,
    user            int NOT NULL,
    title           tinytext NOT NULL,
    description     text NOT NULL,
    subject         tinytext NOT NULL,
    topic           tinytext NOT NULL,

    primary key(id),
    foreign key(user) references user(id) ON DELETE CASCADE
);

create table playlistvideos  (
	no				int NOT NULL,
    playlist        int NOT NULL,
    video           int NOT NULL,

    primary key (playlist, video),
    foreign key (playlist) references playlist(id) ON DELETE CASCADE,
    foreign key (video)    references video(id) ON DELETE CASCADE
);


create table subscription  (
    user            int NOT NULL, 
    playlist        int NOT NULL,

    primary key(user, playlist),
    foreign key(user)     references user(id) ON DELETE CASCADE,
    foreign key(playlist) references playlist(id) ON DELETE CASCADE
);


/* INSERTING VALUES TO DATABASE */  
INSERT INTO user (email, password, type, verified)
VALUES
    -- Password: "do not use in production"
('video-admin@ntnu.no', '$2y$10$7kPPWtRzSWCoAeog/WfQru0rRYQXelbklzg4kvBrcHJIeR5VQfRRe', 'admin', 1),
('lecturer@ntnu.no', '$2y$10$7kPPWtRzSWCoAeog/WfQru0rRYQXelbklzg4kvBrcHJIeR5VQfRRe', 'lecturer', 0);

INSERT INTO video (id, title, description, videoPath, thumbnailPath, subject, topic, user)
    VALUES 
	(1, 'The most amazing video in the world, how it ends might surprise you!', 'test test', '/assets/video/1.mp4',
        '/assets/thumbnail/1.png', 'IMT2019', 'IT', 1),
	(2, 'testtest', 'test testo','/video','/assets/thumbnail/2.png','SMF2019','SMF',1),
        (3, 'The age of VHS', 'The Video Home System (VHS) is a standard for consumer-level analog video recording on tape cassettes. Developed by Victor Company of Japan (JVC) in the early 1970s, it was released in Japan in late 1976 and in the United States in early 1977.', '/assets/video/bd9c36df88db3f19e7dc24ce78fe1ad6f91072bb.webm', '/assets/thumbnail/7fdc2f0b649733affff656008feb978a913032bf.png', 'IMT4302', 'Deprecated video formats', 1),
	(4, 'Rhythm Games', 'Rhythm game or rhythm action is a genre of music-themed action video game that challenges a player\'s sense of rhythm. Games in the genre typically focus on dance or the simulated performance of musical instruments, and require players to press buttons in a sequence dictated on the screen. Doing so causes the game\'s protagonist or avatar to dance or to play their instrument correctly, which increases the player\'s score. Many rhythm games include multiplayer modes in which players compete for the highest score or cooperate as a simulated musical ensemble. While conventional control pads may be used as input devices, rhythm games often feature novel game controllers that emulate musical instruments. Certain dance-based games require the player to physically dance on a mat, with pressure-sensitive pads acting as the input device.', '/assets/video/f1e8a864dde93bbbe15956fe9f32912ff1af9b04.webm', '/assets/thumbnail/1c776176e0a5266f1ae2429d0774a3559cd5bb0b.png', 'IMT4007', 'Video Game Design', 1);

INSERT INTO playlist(id, user, title, description, subject, topic)
	VALUES 
	(1, 1, 'Super cool IT videos', 'This is a collection of super cool IT videos', 'IMT2019', 'Bad memes'),
	(2, 1, 'Music games', 'All kinds of videos related to music games', 'IMT4302', 'Rhythm Games');
	
INSERT INTO playlistvideos(no, playlist, video)
	VALUES 
	(1, 1, 1),
	(2, 1, 2),
        (1, 2, 3),
        (2, 2, 4);

INSERT INTO comment(user, video, comment)
    VALUES
    (1, 1, "WOAH");

INSERT INTO rating(video, user, rating)
    VALUES
    (1, 1, 5),
    (1, 2, 2);


INSERT INTO subscription(user, playlist)
    VALUES
    (1, 1);
