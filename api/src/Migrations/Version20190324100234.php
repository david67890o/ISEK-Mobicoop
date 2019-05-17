<?php declare(strict_types=1);

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190324100234 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ask CHANGE ask_linked_id ask_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE criteria CHANGE car_id car_id INT DEFAULT NULL, CHANGE direction_driver_id direction_driver_id INT DEFAULT NULL, CHANGE direction_passenger_id direction_passenger_id INT DEFAULT NULL, CHANGE ptjourney_id ptjourney_id INT DEFAULT NULL, CHANGE from_time from_time TIME DEFAULT NULL, CHANGE to_date to_date DATE DEFAULT NULL, CHANGE mon_check mon_check TINYINT(1) DEFAULT NULL, CHANGE tue_check tue_check TINYINT(1) DEFAULT NULL, CHANGE wed_check wed_check TINYINT(1) DEFAULT NULL, CHANGE thu_check thu_check TINYINT(1) DEFAULT NULL, CHANGE fri_check fri_check TINYINT(1) DEFAULT NULL, CHANGE sat_check sat_check TINYINT(1) DEFAULT NULL, CHANGE sun_check sun_check TINYINT(1) DEFAULT NULL, CHANGE mon_time mon_time TIME DEFAULT NULL, CHANGE tue_time tue_time TIME DEFAULT NULL, CHANGE wed_time wed_time TIME DEFAULT NULL, CHANGE thu_time thu_time TIME DEFAULT NULL, CHANGE fri_time fri_time TIME DEFAULT NULL, CHANGE sat_time sat_time TIME DEFAULT NULL, CHANGE sun_time sun_time TIME DEFAULT NULL, CHANGE mon_margin_time mon_margin_time INT DEFAULT NULL, CHANGE is_driver is_driver TINYINT(1) DEFAULT NULL, CHANGE is_passenger is_passenger TINYINT(1) DEFAULT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT NULL, CHANGE tue_margin_time tue_margin_time INT DEFAULT NULL, CHANGE wed_margin_time wed_margin_time INT DEFAULT NULL, CHANGE thu_margin_time thu_margin_time INT DEFAULT NULL, CHANGE fri_margin_time fri_margin_time INT DEFAULT NULL, CHANGE sat_margin_time sat_margin_time INT DEFAULT NULL, CHANGE sun_margin_time sun_margin_time INT DEFAULT NULL, CHANGE price_km price_km NUMERIC(4, 2) DEFAULT NULL, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE margin_time margin_time INT DEFAULT NULL, CHANGE min_time min_time TIME DEFAULT NULL, CHANGE max_time max_time TIME DEFAULT NULL, CHANGE mon_min_time mon_min_time TIME DEFAULT NULL, CHANGE mon_max_time mon_max_time TIME DEFAULT NULL, CHANGE tue_min_time tue_min_time TIME DEFAULT NULL, CHANGE tue_max_time tue_max_time TIME DEFAULT NULL, CHANGE wed_min_time wed_min_time TIME DEFAULT NULL, CHANGE wed_max_time wed_max_time TIME DEFAULT NULL, CHANGE thu_min_time thu_min_time TIME DEFAULT NULL, CHANGE thu_max_time thu_max_time TIME DEFAULT NULL, CHANGE fri_min_time fri_min_time TIME DEFAULT NULL, CHANGE fri_max_time fri_max_time TIME DEFAULT NULL, CHANGE sat_min_time sat_min_time TIME DEFAULT NULL, CHANGE sat_max_time sat_max_time TIME DEFAULT NULL, CHANGE sun_min_time sun_min_time TIME DEFAULT NULL, CHANGE sun_max_time sun_max_time TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal CHANGE user_id user_id INT DEFAULT NULL, CHANGE proposal_linked_id proposal_linked_id INT DEFAULT NULL, CHANGE criteria_id criteria_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE waypoint CHANGE proposal_id proposal_id INT DEFAULT NULL, CHANGE matching_id matching_id INT DEFAULT NULL, CHANGE ask_id ask_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE car CHANGE color color VARCHAR(45) DEFAULT NULL, CHANGE siv siv VARCHAR(45) DEFAULT NULL, CHANGE price_km price_km NUMERIC(4, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE given_name given_name VARCHAR(100) DEFAULT NULL, CHANGE family_name family_name VARCHAR(100) DEFAULT NULL, CHANGE password password VARCHAR(100) DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL, CHANGE birth_date birth_date DATE DEFAULT NULL, CHANGE telephone telephone VARCHAR(100) DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT NULL, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zone DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE zone CHANGE zone zoneid INT NOT NULL');
        $this->addSql('ALTER TABLE zone ADD PRIMARY KEY (direction_id, zoneid, `precision`)');
        $this->addSql('ALTER TABLE address CHANGE user_id user_id INT DEFAULT NULL, CHANGE street_address street_address VARCHAR(255) DEFAULT NULL, CHANGE postal_code postal_code VARCHAR(15) DEFAULT NULL, CHANGE address_locality address_locality VARCHAR(100) DEFAULT NULL, CHANGE address_country address_country VARCHAR(100) DEFAULT NULL, CHANGE latitude latitude NUMERIC(10, 6) DEFAULT NULL, CHANGE longitude longitude NUMERIC(10, 6) DEFAULT NULL, CHANGE elevation elevation INT DEFAULT NULL, CHANGE name name VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE direction CHANGE ascend ascend INT DEFAULT NULL, CHANGE descend descend INT DEFAULT NULL, CHANGE bbox_min_lon bbox_min_lon NUMERIC(10, 6) DEFAULT NULL, CHANGE bbox_min_lat bbox_min_lat NUMERIC(10, 6) DEFAULT NULL, CHANGE bbox_max_lon bbox_max_lon NUMERIC(10, 6) DEFAULT NULL, CHANGE bbox_max_lat bbox_max_lat NUMERIC(10, 6) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptarrival CHANGE individual_stop_id individual_stop_id INT DEFAULT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptdeparture CHANGE individual_stop_id individual_stop_id INT DEFAULT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptjourney CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration VARCHAR(100) DEFAULT NULL, CHANGE price price NUMERIC(4, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptleg CHANGE ptjourney_id ptjourney_id INT DEFAULT NULL, CHANGE ptdeparture_id ptdeparture_id INT DEFAULT NULL, CHANGE ptarrival_id ptarrival_id INT DEFAULT NULL, CHANGE travel_mode_id travel_mode_id INT DEFAULT NULL, CHANGE ptline_id ptline_id INT DEFAULT NULL, CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE magnetic_direction magnetic_direction VARCHAR(10) DEFAULT NULL, CHANGE relative_direction relative_direction VARCHAR(10) DEFAULT NULL, CHANGE direction direction VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptline CHANGE ptcompany_id ptcompany_id INT DEFAULT NULL, CHANGE travel_mode_id travel_mode_id INT DEFAULT NULL, CHANGE number number VARCHAR(10) DEFAULT NULL, CHANGE origin origin VARCHAR(100) DEFAULT NULL, CHANGE destination destination VARCHAR(100) DEFAULT NULL, CHANGE direction direction VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE ptstep CHANGE ptleg_id ptleg_id INT DEFAULT NULL, CHANGE ptdeparture_id ptdeparture_id INT DEFAULT NULL, CHANGE ptarrival_id ptarrival_id INT DEFAULT NULL, CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE magnetic_direction magnetic_direction VARCHAR(10) DEFAULT NULL, CHANGE relative_direction relative_direction VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE event CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE image CHANGE event_id event_id INT DEFAULT NULL, CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE alt alt VARCHAR(255) DEFAULT NULL, CHANGE crop_x1 crop_x1 INT DEFAULT NULL, CHANGE crop_y1 crop_y1 INT DEFAULT NULL, CHANGE crop_x2 crop_x2 INT DEFAULT NULL, CHANGE crop_y2 crop_y2 INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE address CHANGE user_id user_id INT DEFAULT NULL, CHANGE street_address street_address VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE postal_code postal_code VARCHAR(15) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address_locality address_locality VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE address_country address_country VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE latitude latitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE longitude longitude NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE elevation elevation INT DEFAULT NULL, CHANGE name name VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ask CHANGE ask_linked_id ask_linked_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE car CHANGE color color VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE siv siv VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE price_km price_km NUMERIC(4, 2) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE criteria CHANGE car_id car_id INT DEFAULT NULL, CHANGE direction_driver_id direction_driver_id INT DEFAULT NULL, CHANGE direction_passenger_id direction_passenger_id INT DEFAULT NULL, CHANGE ptjourney_id ptjourney_id INT DEFAULT NULL, CHANGE is_driver is_driver TINYINT(1) DEFAULT \'NULL\', CHANGE is_passenger is_passenger TINYINT(1) DEFAULT \'NULL\', CHANGE from_time from_time TIME DEFAULT \'NULL\', CHANGE min_time min_time TIME DEFAULT \'NULL\', CHANGE max_time max_time TIME DEFAULT \'NULL\', CHANGE margin_time margin_time INT DEFAULT NULL, CHANGE to_date to_date DATE DEFAULT \'NULL\', CHANGE mon_check mon_check TINYINT(1) DEFAULT \'NULL\', CHANGE tue_check tue_check TINYINT(1) DEFAULT \'NULL\', CHANGE wed_check wed_check TINYINT(1) DEFAULT \'NULL\', CHANGE thu_check thu_check TINYINT(1) DEFAULT \'NULL\', CHANGE fri_check fri_check TINYINT(1) DEFAULT \'NULL\', CHANGE sat_check sat_check TINYINT(1) DEFAULT \'NULL\', CHANGE sun_check sun_check TINYINT(1) DEFAULT \'NULL\', CHANGE mon_time mon_time TIME DEFAULT \'NULL\', CHANGE mon_min_time mon_min_time TIME DEFAULT \'NULL\', CHANGE mon_max_time mon_max_time TIME DEFAULT \'NULL\', CHANGE tue_time tue_time TIME DEFAULT \'NULL\', CHANGE tue_min_time tue_min_time TIME DEFAULT \'NULL\', CHANGE tue_max_time tue_max_time TIME DEFAULT \'NULL\', CHANGE wed_time wed_time TIME DEFAULT \'NULL\', CHANGE wed_min_time wed_min_time TIME DEFAULT \'NULL\', CHANGE wed_max_time wed_max_time TIME DEFAULT \'NULL\', CHANGE thu_time thu_time TIME DEFAULT \'NULL\', CHANGE thu_min_time thu_min_time TIME DEFAULT \'NULL\', CHANGE thu_max_time thu_max_time TIME DEFAULT \'NULL\', CHANGE fri_time fri_time TIME DEFAULT \'NULL\', CHANGE fri_min_time fri_min_time TIME DEFAULT \'NULL\', CHANGE fri_max_time fri_max_time TIME DEFAULT \'NULL\', CHANGE sat_time sat_time TIME DEFAULT \'NULL\', CHANGE sat_min_time sat_min_time TIME DEFAULT \'NULL\', CHANGE sat_max_time sat_max_time TIME DEFAULT \'NULL\', CHANGE sun_time sun_time TIME DEFAULT \'NULL\', CHANGE sun_min_time sun_min_time TIME DEFAULT \'NULL\', CHANGE sun_max_time sun_max_time TIME DEFAULT \'NULL\', CHANGE mon_margin_time mon_margin_time INT DEFAULT NULL, CHANGE tue_margin_time tue_margin_time INT DEFAULT NULL, CHANGE wed_margin_time wed_margin_time INT DEFAULT NULL, CHANGE thu_margin_time thu_margin_time INT DEFAULT NULL, CHANGE fri_margin_time fri_margin_time INT DEFAULT NULL, CHANGE sat_margin_time sat_margin_time INT DEFAULT NULL, CHANGE sun_margin_time sun_margin_time INT DEFAULT NULL, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE multi_transport_mode multi_transport_mode TINYINT(1) DEFAULT \'NULL\', CHANGE price_km price_km NUMERIC(4, 2) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE direction CHANGE ascend ascend INT DEFAULT NULL, CHANGE descend descend INT DEFAULT NULL, CHANGE bbox_min_lon bbox_min_lon NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE bbox_min_lat bbox_min_lat NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE bbox_max_lon bbox_max_lon NUMERIC(10, 6) DEFAULT \'NULL\', CHANGE bbox_max_lat bbox_max_lat NUMERIC(10, 6) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE event CHANGE url url VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE image CHANGE event_id event_id INT DEFAULT NULL, CHANGE title title VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE alt alt VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE crop_x1 crop_x1 INT DEFAULT NULL, CHANGE crop_y1 crop_y1 INT DEFAULT NULL, CHANGE crop_x2 crop_x2 INT DEFAULT NULL, CHANGE crop_y2 crop_y2 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal CHANGE proposal_linked_id proposal_linked_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE criteria_id criteria_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ptarrival CHANGE individual_stop_id individual_stop_id INT DEFAULT NULL, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ptdeparture CHANGE individual_stop_id individual_stop_id INT DEFAULT NULL, CHANGE name name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ptjourney CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE price price NUMERIC(4, 2) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE ptleg CHANGE ptjourney_id ptjourney_id INT DEFAULT NULL, CHANGE ptdeparture_id ptdeparture_id INT DEFAULT NULL, CHANGE ptarrival_id ptarrival_id INT DEFAULT NULL, CHANGE travel_mode_id travel_mode_id INT DEFAULT NULL, CHANGE ptline_id ptline_id INT DEFAULT NULL, CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE magnetic_direction magnetic_direction VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE relative_direction relative_direction VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE direction direction VARCHAR(45) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ptline CHANGE ptcompany_id ptcompany_id INT DEFAULT NULL, CHANGE travel_mode_id travel_mode_id INT DEFAULT NULL, CHANGE number number VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE origin origin VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE destination destination VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE direction direction VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE ptstep CHANGE ptleg_id ptleg_id INT DEFAULT NULL, CHANGE ptdeparture_id ptdeparture_id INT DEFAULT NULL, CHANGE ptarrival_id ptarrival_id INT DEFAULT NULL, CHANGE distance distance INT DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE magnetic_direction magnetic_direction VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE relative_direction relative_direction VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE user CHANGE given_name given_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE family_name family_name VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE nationality nationality VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE birth_date birth_date DATE DEFAULT \'NULL\', CHANGE telephone telephone VARCHAR(100) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE max_detour_duration max_detour_duration INT DEFAULT NULL, CHANGE max_detour_distance max_detour_distance INT DEFAULT NULL, CHANGE any_route_as_passenger any_route_as_passenger TINYINT(1) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE waypoint CHANGE proposal_id proposal_id INT DEFAULT NULL, CHANGE matching_id matching_id INT DEFAULT NULL, CHANGE ask_id ask_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE zone DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE zone CHANGE zoneid zone INT NOT NULL');
        $this->addSql('ALTER TABLE zone ADD PRIMARY KEY (direction_id, zone, `precision`)');
    }
}
