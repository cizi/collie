SET FOREIGN_KEY_CHECKS=0;

################################### migrace psů ###############################
INSERT INTO appdata_pes (
  `ID`,
  `TitulyPredJmenem`,
  `TitulyZaJmenem`,
  `Jmeno`,
  `DatNarozeni`,
  `DatUmrti`,
  `UmrtiKomentar`,
  `Pohlavi`,
  `Plemeno`,
  `Barva`,
  `Srst`,
  `BarvaKomentar`,
  `CisloZapisu`,
  `PCisloZapisu`,
  `Cip`,
  `Tetovani`,
  `ZdravotniKomentar`,
  `Varlata`,
  `Skus`,
  `Zuby`,
  `ZubyKomentar`,
  `Chovnost`,
  `ChovnyKomentar`,
  `Posudek`,
  `Zkousky`,
  `TitulyKomentar`,
  `Oceneni`,
  `Zavody`,
  `oID`,
  `mID`,
  `Komentar`,
  `PosledniZmena`,
  `Vyska`,
  `Vaha`,
  `Bonitace`,
  `ImpFrom`,
  `ImpID`,
  `oIDupdate`,
  `mIDupdate`
) 
SELECT `ID`,
  `TitulyPredJmenem`,
  `TitulyZaJmenem`,
  `Jmeno`,
  `DatNarozeni`,
  `DatUmrti`,
  `UmrtiKomentar`,
  IF(`Pohlavi` = 0, NULL, IF (`Pohlavi` = 1, 29, 30)),
  CASE `Plemeno`
    WHEN 0 THEN NULL
    WHEN 1 THEN 17
    WHEN 2 THEN 18
    WHEN 3 THEN 19
  END,
  CASE
    WHEN `Barva` = 0 THEN NULL
    WHEN `Barva` = 1 THEN 23
    WHEN `Barva` = 2 THEN 22
    WHEN `Barva` = 3 THEN 21
    WHEN `Barva` = 4 THEN 24
    WHEN `Barva` = 15 THEN NULL
  END,
  CASE
    WHEN `Srst` = 0 THEN NULL
    WHEN `Srst` = 1 THEN 45
    WHEN `Srst` = 2 THEN 44
  END,
  `BarvaKomentar`,
  `CisloZapisu`,
  `PCisloZapisu`,
  `Cip`,
  `Tetovani`,
  `ZdravotniKomentar`,
  CASE
    WHEN `Varlata` = 0 THEN NULL
    WHEN `Varlata` = 1 THEN 47
    WHEN `Varlata` = 2 THEN 48
    WHEN `Varlata` = 3 THEN 49
    WHEN `Varlata` = 4 THEN 50
  END,
  CASE
    WHEN `Skus` = 0 THEN NULL
    WHEN `Skus` = 1 THEN 36
    WHEN `Skus` = 2 THEN 37
    WHEN `Skus` = 3 THEN 38
    WHEN `Skus` = 4 THEN 39
    WHEN `Skus` = 5 THEN 40
    WHEN `Skus` = 6 THEN 41
    WHEN `Skus` = 7 THEN 42
  END,
  `Zuby`,
  `ZubyKomentar`,
  CASE
    WHEN `Chovnost` = 0 THEN NULL
    WHEN `Chovnost` = 1 THEN 26
    WHEN `Chovnost` = 2 THEN 27
    WHEN `Chovnost` = 3 THEN 28
  END,
  `ChovnyKomentar`,
  `Posudek`,
  `Zkousky`,
  `TitulyKomentar`,
  `Oceneni`,
  `Zavody`,
  IF(`oID` = 0, NULL, `oID`),
  IF(`mID` = 0, NULL, `mID`),
  `Komentar`,
  `PosledniZmena`,
  IF(`Vyska` = 0, 0, `Vyska`/10),
  IF(`Vaha` = 0, 0, `Vaha`/10),
  `Bonitace`,
  `ImpFrom`,
  `ImpID`,
  `oIDupdate`,
  `mIDupdate` from `pes`;
  
# RENAME TABLE pes TO migrated_pes;

###############################################################################
################################# migrace veterinářů ##########################

INSERT INTO appdata_veterinar (
  `ID`,
  `Jmeno`,
  `Prijmeni`,
  `TitulyPrefix`,
  `TitulySuffix`,
  `Ulice`,
  `Mesto`,
  `PSC`
)
SELECT `ID`,
  `Jmeno`,
  `Prijmeni`,
  `TitulyPrefix`,
  `TitulySuffix`,
  `Ulice`,
  `Mesto`,
  `PSC`
FROM veterinar; 
#RENAME TABLE veterinar to migrated_veterinar;

###############################################################################
################################ migrace zdravi ###############################

INSERT INTO appdata_zdravi (
  `ID`,
  `pID`,
  `Typ`,
  `Vysledek`,
  `Komentar`,
  `Datum`,
  `Veterinar`	
)
SELECT `ID`,
  `pID`,
  CASE `Typ`
    WHEN 1 THEN 58 
    WHEN 2 THEN 59
    WHEN 3 THEN 60
    WHEN 4 THEN 61
    WHEN 5 THEN 62
    WHEN 6 THEN 64
    WHEN 7 THEN 65
    WHEN 8 THEN 66
    WHEN 9 THEN 67
    WHEN 10 THEN 63 
    WHEN 11 THEN 68
    WHEN 12 THEN 69 
  END,
  `Vysledek`,
  `Komentar`,
  `Datum`,   
  IF(`Veterinar` = 0, NULL, `Veterinar`)
FROM zdravi;
# RENAME TABLE zdravi TO migrated_zdravi;

###############################################################################

############### ROZHODCI #################

INSERT INTO appdata_rozhodci (
  `ID`,
  `Jmeno`,
  `Prijmeni`,
  `TitulyPrefix`,
  `TitulySuffix`,
  `Ulice`,
  `Mesto`,
  `PSC`
)
SELECT `ID`,
  `Jmeno`,
  `Prijmeni`,
  `TitulyPrefix`,
  `TitulySuffix`,
  `Ulice`,
  `Mesto`,
  `PSC`
FROM rozhodci;
#RENAME TABLE rozhodci to migrated_rozhodci;

#################################################

###################### VYSTAVA ##################
 INSERT INTO appdata_vystava (
  `ID`,
  `Typ`,
  `Datum`,
  `Nazev`,
  `Misto`,
  `Hotovo`,
  `Rozhodci`
)
SELECT `ID`,
  CASE `Typ`
    WHEN 'M' THEN 92
    WHEN 'N' THEN 93
    WHEN 'O' THEN 94
    WHEN 'K' THEN 95
    WHEN 'L' THEN 96
    WHEN 'S' THEN 97
    WHEN 'W' THEN 98
    WHEN 'E' THEN 99
    WHEN 'D' THEN 100
    WHEN 'B' THEN 101
  END,
  `Datum`,
  `Nazev`,
  `Misto`,
  `Hotovo`,
  IF(`Rozhodci` = 0, NULL, `Rozhodci`)
FROM vystava;
#RENAME TABLE vystava to migrated_vystava;
############################################

######## prihlaska vrhu #############
INSERT INTO appdata_prihlaska (
  `ID`,
  `Datum`,
  `oID`,
  `mID`,
  `DatumNarozeni`,
  `Data`,
  `Formular`,
  `Zavedeno`,
  `Plemeno`
)
SELECT
  `ID`,
  IF(`Datum` != '', `Datum`, NULL),
  `oID`,
  `mID`,
  IF(`DatumNarozeni` != '', `DatumNarozeni`, NULL),
  `Data`,
  `Formular`,
  `Zavedeno`,
  CASE `Plemeno`
    WHEN 0 THEN NULL
    WHEN 1 THEN 17
    WHEN 2 THEN 18
    WHEN 3 THEN 19
  END
FROM prihlaska;
#RENAME TABLE prihlaska to migrated_prihlaska;

######################################

SET FOREIGN_KEY_CHECKS=1;