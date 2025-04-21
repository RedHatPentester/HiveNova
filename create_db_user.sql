-- SQL script to create a new MySQL user and required tables for the HiveNova vulnerable lab

CREATE DATABASE IF NOT EXISTS hivenova;

CREATE USER 'vulnuser'@'localhost' IDENTIFIED BY 'vulnpassword';

GRANT ALL PRIVILEGES ON hivenova.* TO 'vulnuser'@'localhost';

FLUSH PRIVILEGES;

USE hivenova;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL
);

DROP TABLE IF EXISTS staff;

CREATE TABLE staff (
    UUID VARCHAR(36) PRIMARY KEY,
    Name VARCHAR(255),
    Role VARCHAR(50),
    Department VARCHAR(100),
    username VARCHAR(100),
    password VARCHAR(100)
);

DROP TABLE IF EXISTS patient_records;

CREATE TABLE patient_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    Name VARCHAR(255),
    Age INT,
    Illness VARCHAR(255),
    LastVisit DATE,
    Doctor VARCHAR(255)
);