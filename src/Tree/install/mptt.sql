/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : cherry

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2022-08-22 10:26:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `category`
-- ----------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(50) NOT NULL DEFAULT '',
  `Description` varchar(255) DEFAULT NULL,
  `Lft` int(11) NOT NULL DEFAULT '0',
  `Rgt` int(11) NOT NULL DEFAULT '0',
  `Parent` int(11) NOT NULL DEFAULT '0',
  `Is_active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of category
-- ----------------------------
INSERT INTO `category` VALUES ('1', 'Food', null, '1', '28', '0', '1');
INSERT INTO `category` VALUES ('2', 'Food33', '33', '2', '27', '1', '9');
INSERT INTO `category` VALUES ('3', 'Food3', null, '3', '26', '2', '8');
INSERT INTO `category` VALUES ('4', 'Food444', '444', '4', '5', '3', '4');
INSERT INTO `category` VALUES ('5', 'Food33', null, '6', '25', '3', '0');
INSERT INTO `category` VALUES ('6', 'Food3', null, '7', '24', '5', '0');
INSERT INTO `category` VALUES ('7', 'Food444', null, '8', '23', '6', '0');
INSERT INTO `category` VALUES ('8', 'Food', null, '9', '22', '7', '0');
INSERT INTO `category` VALUES ('9', 'Food33', null, '10', '21', '8', '0');
INSERT INTO `category` VALUES ('10', 'Food3', null, '11', '20', '9', '0');
INSERT INTO `category` VALUES ('11', 'Food444', null, '12', '13', '10', '0');
INSERT INTO `category` VALUES ('12', 'Food33', null, '14', '19', '10', '0');
INSERT INTO `category` VALUES ('13', 'Food3', null, '15', '18', '12', '0');
INSERT INTO `category` VALUES ('14', 'Food444', null, '16', '17', '13', '0');
