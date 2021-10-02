create table userbase(
   sid varchar(50) not null,
   lname varchar(50) not null,
   fname varchar(50) not null,
   yr varchar(50) not null,
   tutg varchar(50) not null,
   house varchar(50) not null,
   email varchar(50) not null,
   primary key(sid)
);

create table users(
    uid varchar(20) not null,
    utype varchar(1) not null,
    password varchar(64) not null,
    fname varchar(50) not null,
    lname varchar(50) not null,
    email varchar(50) unique not null,
    salt varchar(10) not null,
    primary key(uid)
);

create table students(
    uid varchar(20) not null,
    tutg varchar(10) not null,
    house varchar(15) not null,
    yr varchar(10) not null,
    primary key (uid),
    foreign key (uid) references users(uid)
);

create table mentors(
    uid varchar(20) not null,
    verified bit not null,
    img integer auto_increment not null unique,
    primary key (uid),
    foreign key (uid) references users(uid)
);

create table subjects(
    subjectid integer unsigned auto_increment not null,
    subjectname varchar(20),
    ylv integer not null,
    primary key(subjectid)
);

create table usersubjects(
    uid varchar(20) not null,
    subjectid integer unsigned not null,
    primary key(uid,subjectid),
    foreign key (uid) references users(uid),
    foreign key (subjectid) references subjects(subjectid)
);

create table sessions(
    sessionid integer unsigned auto_increment not null,
    sdate date not null,
    stime time not null,
    etime time not null,
    room varchar(20) not null,
    mentor varchar(20),
    subid integer unsigned not null,
    slimit integer,
    stat varchar(1),
    initiator varchar(20) not null,
    primary key(sessionid),
    foreign key (initiator) references users(uid),
    foreign key (mentor) references users(uid),
    foreign key (subid) references subjects(subjectid)
);

create table studentsessions(
    studentid varchar(20) not null,
    sessionid integer unsigned not null,
    mrating integer,
    srating integer,
    note varchar(100),
    stat varchar(1),
    attendance bit not null,
    primary key (sessionid,studentid),
    foreign key (sessionid) references sessions(sessionid),
    foreign key (studentid) references users(uid)
);

create table rooms(
    room varchar(20) not null,
    primary key(room)
);




INSERT INTO `users` (`uid`, `utype`, `password`, `fname`, `lname`, `email`, `salt`) VALUES ('mentor', 'm', 'e8735b3e661833e05d4afe55ae6e191b81dffe95f5c97ec554d0e3322ad86328', 'Mentor', 'Man', 'mentorman@gmail.com', 'grape');
INSERT INTO `mentors` (`uid`, `verified`, `img`) VALUES ('mentor', b'1','1');