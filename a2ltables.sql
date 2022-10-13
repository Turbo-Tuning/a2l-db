CREATE TABLE header_info (
	rid INTEGER NOT NULL,
	name VARCHAR(256),
	longDesc VARCHAR(1025),
	offset VARCHAR(256),
	addr_epk VARCHAR(256),
	byte_order INTEGER,
	ecu VARCHAR(256),
	epk VARCHAR(256),
	a2l_filename TEXT,
	a2l_filesize TEXT,
	PRIMARY KEY (rid)
);

CREATE TABLE value_pairs (
	rid INTEGER NOT NULL,
	value_pair VARCHAR(256),
	compu_vtabs_rid INTEGER,
	PRIMARY KEY (rid)
);

CREATE TABLE compu_vtabs (
	rid INTEGER NOT NULL, 
	name VARCHAR(1025) NOT NULL, 
	longDesc VARCHAR(256) NOT NULL, 
	conversionType VARCHAR(256) NOT NULL, 
	numberValuePairs INTEGER NOT NULL, 
	PRIMARY KEY (rid)
);
INSERT INTO compu_vtabs (name, longDesc, conversionType, numberValuePairs) VALUES ("","","","");


CREATE TABLE compu_methods (
	rid INTEGER NOT NULL, 
	name VARCHAR(1025) NOT NULL, 
	longDesc VARCHAR(256) NOT NULL, 
	conversionType VARCHAR(256) NOT NULL, 
	format VARCHAR(256) NOT NULL, 
	uom VARCHAR(256) NOT NULL,
	compu_type VARCHAR(256) NOT NULL,
	coeff_a FLOAT NOT NULL, 
	coeff_b FLOAT NOT NULL, 
	coeff_c FLOAT NOT NULL, 
	coeff_d FLOAT NOT NULL, 
	coeff_e FLOAT NOT NULL, 
	coeff_f FLOAT NOT NULL,
	PRIMARY KEY (rid)
);
CREATE INDEX "compu_method_name" ON "compu_methods" (
	"name"
);

CREATE TABLE record_layouts (
	rid INTEGER NOT NULL, 
	name VARCHAR(1025) NOT NULL,
	axis_pts_x INTEGER,
	axis_pts_y INTEGER,
	fnc_values INTEGER,
	PRIMARY KEY (rid)
);

CREATE TABLE axis_descr (
	rid INTEGER NOT NULL, 
	name VARCHAR(256) NOT NULL, 
	compu_method TEXT, 
	conversion VARCHAR(1025) NOT NULL, 
	maxAxisPoints INTEGER NOT NULL, 
	lowerLimit FLOAT NOT NULL, 
	upperLimit FLOAT NOT NULL,
	PRIMARY KEY (rid)
);

CREATE TABLE measurements (
	rid INTEGER NOT NULL, 
	name VARCHAR(1025) NOT NULL, 
	longDesc VARCHAR(256) NOT NULL, 
	datatype VARCHAR(1025) NOT NULL, 
	compu_method TEXT, 
	resolution INTEGER NOT NULL, 
	accuracy FLOAT NOT NULL, 
	lowerLimit FLOAT NOT NULL, 
	upperLimit FLOAT NOT NULL, 
	format VARCHAR(256) NOT NULL, 
	addr VARCHAR(256),
	PRIMARY KEY (rid)
);

CREATE TABLE characteristics (
	rid INTEGER NOT NULL, 
	name VARCHAR(1025) NOT NULL,
	longDesc VARCHAR(256) NOT NULL, 
	type VARCHAR(256) NOT NULL, 
	addr VARCHAR(10) NOT NULL, 
	record_layout TEXT, 
	maxDiff FLOAT NOT NULL, 
	compu_method TEXT, 
	lowerLimit FLOAT NOT NULL, 
	upperLimit FLOAT NOT NULL,
	format varchar(10),
	PRIMARY KEY (rid)
);

CREATE TABLE functions (
	rid INTEGER NOT NULL,
	name VARCHAR(1025) NOT NULL,
	longDesc VARCHAR(256) NOT NULL,
	PRIMARY KEY (rid)
);

CREATE TABLE def_characteristics (
	rid INTEGER,
	function TEXT,
	characteristic TEXT,
	PRIMARY KEY (rid)
);

CREATE TABLE ref_characteristics (
	rid INTEGER,
	function TEXT,
	characteristic TEXT,
	PRIMARY KEY (rid)
);
