-- --------------------------------------------------------
-- BASE DE DATOS: panther
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS panther;
USE panther;

-- ELIMINAR EN ORDEN CORRECTO PARA EVITAR ERRORES DE FOREIGN KEY
DROP TABLE IF EXISTS `person`;
DROP TABLE IF EXISTS `cities`;
DROP TABLE IF EXISTS `states`;
DROP TABLE IF EXISTS `countries`;
DROP TABLE IF EXISTS `document_type`;
DROP TABLE IF EXISTS `j4user`;
DROP TABLE IF EXISTS `j4rol`;

-- --------------------------------------------------------
-- TABLA: j4rol (Roles del sistema)
-- --------------------------------------------------------
CREATE TABLE `j4rol` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `j4rol` (`name`, `description`) VALUES
('admin', 'Administrator access'),
('user', 'Generic user');

-- --------------------------------------------------------
-- TABLA: j4user (Usuarios del sistema)
-- --------------------------------------------------------
CREATE TABLE `j4user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(254) NOT NULL,
  `password` varchar(128) NOT NULL,
  `keyAPI` varchar(60) NOT NULL,
  `roles` int(11) NOT NULL,
  `dateDelete` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `j4user` (`user`),
  FOREIGN KEY (`roles`) REFERENCES j4rol(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `j4user` (`user`, `password`, `keyAPI`, `roles`) 
VALUES (
  'admin',
  '$2y$10$rCdykaN0YQL/H4VJW0RKae69B2QekbrO3Iuj8OxFy/V1syHOmpfmO',
  '750e8b43e5ed564462c90ef0d382db26',
  1
);

-- --------------------------------------------------------
-- TABLA: document_type
-- --------------------------------------------------------
CREATE TABLE `document_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_long` varchar(100) NOT NULL,
  `name_short` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO document_type (name_long, name_short) VALUES
('Cédula de Ciudadanía', 'CC'),
('Tarjeta de Identidad', 'TI'),
('Pasaporte', 'PA'),
('Cédula de Extranjería', 'CE');

-- --------------------------------------------------------
-- TABLAS: Países, Estados, Ciudades
-- --------------------------------------------------------
CREATE TABLE `countries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `country_id` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`country_id`) REFERENCES countries(id)
);

CREATE TABLE `cities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `state_id` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`state_id`) REFERENCES states(id)
);

-- Insertar datos base
INSERT INTO countries (name) VALUES ('Colombia');
INSERT INTO states (name, country_id) VALUES ('Cundinamarca', 1);
INSERT INTO cities (name, state_id) VALUES ('Bogotá', 1);

-- --------------------------------------------------------
-- TABLA: person
-- --------------------------------------------------------
CREATE TABLE `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `dateDelete` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`document_type_id`) REFERENCES document_type(id),
  FOREIGN KEY (`country_id`) REFERENCES countries(id),
  FOREIGN KEY (`state_id`) REFERENCES states(id),
  FOREIGN KEY (`city_id`) REFERENCES cities(id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO person (name, lastName, phone, document_type_id, country_id, state_id, city_id)
VALUES ('Nicolás', 'Pinzón', '3419478', 1, 1, 1, 1);
