CREATE TABLE `appdata_poradi_vysetreni` (
    `enum_poradi` int NOT NULL,
    `zobrazeni_poradi` int NOT NULL
) ENGINE='InnoDB';

ALTER TABLE `appdata_poradi_vysetreni` ADD FOREIGN KEY (`enum_poradi`) REFERENCES `enum_item` (`order`) ON DELETE RESTRICT ON UPDATE RESTRICT;

