-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Creato il: Set 26, 2026 alle 04:20
-- Versione del server: 11.4.13-MariaDB
-- Versione PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40185200_vineria`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `Eventi`
--

CREATE TABLE `Eventi` (
  `Id` int(11) NOT NULL,
  `Titolo` varchar(255) NOT NULL,
  `Descrizione` text NOT NULL,
  `Data` text NOT NULL,
  `OraInizio` text NOT NULL,
  `OraFine` text NOT NULL,
  `Citta` text NOT NULL,
  `Indirizzo` text NOT NULL,
  `PostiTotali` smallint(6) NOT NULL,
  `Costo` decimal(6,2) NOT NULL,
  `ScadenzaIscrizione` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Iscrizioni`
--

CREATE TABLE `Iscrizioni` (
  `Id` int(11) NOT NULL,
  `IdEvento` int(11) NOT NULL,
  `Nome` varchar(255) NOT NULL,
  `Cognome` varchar(255) NOT NULL,
  `Cellulare` varchar(255) NOT NULL,
  `MetodoPagamento` char(1) DEFAULT '/',
  `PaymentId` varchar(255) DEFAULT NULL,
  `Data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Paragrafi`
--

CREATE TABLE `Paragrafi` (
  `Id` int(11) NOT NULL,
  `Testo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Pagina` enum('home','about','eventi','footer') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `PendingPagamenti`
--

CREATE TABLE `PendingPagamenti` (
  `Id` int(11) NOT NULL,
  `PaymentId` text NOT NULL,
  `Cellulare` text NOT NULL,
  `Cognome` text NOT NULL,
  `Nome` text NOT NULL,
  `IdEvento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Vini`
--

CREATE TABLE `Vini` (
  `Id` int(11) NOT NULL,
  `Marca` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nome` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Descrizione` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Anno` smallint(6) DEFAULT NULL,
  `Costo` decimal(6,2) DEFAULT NULL,
  `Quantita` decimal(6,2) NOT NULL,
  `Gradazione` decimal(3,1) DEFAULT NULL,
  `Offerta` decimal(6,2) DEFAULT NULL,
  `Evidenzia` tinyint(1) DEFAULT 0,
  `Effervescenza` enum('Fermo','Mosso','Frizzante','Spumante') NOT NULL,
  `Colore` enum('Rosso','Bianco','Rosato','Arancione','Grigio') NOT NULL,
  `Denominazione` varchar(255) DEFAULT NULL,
  `Regione` varchar(255) DEFAULT NULL,
  `Vitigno` varchar(255) NOT NULL,
  `Bio` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Eventi`
--
ALTER TABLE `Eventi`
  ADD PRIMARY KEY (`Id`);

--
-- Indici per le tabelle `Iscrizioni`
--
ALTER TABLE `Iscrizioni`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IdEvento` (`IdEvento`);

--
-- Indici per le tabelle `Paragrafi`
--
ALTER TABLE `Paragrafi`
  ADD PRIMARY KEY (`Id`);

--
-- Indici per le tabelle `PendingPagamenti`
--
ALTER TABLE `PendingPagamenti`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `PaymentId` (`PaymentId`) USING HASH,
  ADD KEY `IdEvento` (`IdEvento`);

--
-- Indici per le tabelle `Vini`
--
ALTER TABLE `Vini`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Eventi`
--
ALTER TABLE `Eventi`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Iscrizioni`
--
ALTER TABLE `Iscrizioni`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `PendingPagamenti`
--
ALTER TABLE `PendingPagamenti`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Vini`
--
ALTER TABLE `Vini`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `Iscrizioni`
--
ALTER TABLE `Iscrizioni`
  ADD CONSTRAINT `Iscrizioni_ibfk_1` FOREIGN KEY (`IdEvento`) REFERENCES `Eventi` (`Id`);

--
-- Limiti per la tabella `PendingPagamenti`
--
ALTER TABLE `PendingPagamenti`
  ADD CONSTRAINT `PendingPagamenti_ibfk_1` FOREIGN KEY (`IdEvento`) REFERENCES `Eventi` (`Id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
