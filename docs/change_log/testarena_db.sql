-- phpMyAdmin SQL Dump
-- version 4.4.12
-- http://www.phpmyadmin.net
--
-- Host: testarena.nazwa.pl:3307
-- Czas generowania: 02 Paź 2015, 12:44
-- Wersja serwera: 5.5.43-MariaDB-log
-- Wersja PHP: 5.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `testarena_47`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `attachment`
--

CREATE TABLE `attachment` (
  `id` int(10) unsigned NOT NULL,
  `file_id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  `create_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `attachment_type`
--

CREATE TABLE `attachment_type` (
  `id` tinyint(2) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `attachment_type`
--

INSERT INTO `attachment_type` (`id`, `name`) VALUES
(5, 'DEFECT_ATTACHMENT'),
(4, 'DOCUMENTATION'),
(3, 'PROJECT_PLAN'),
(2, 'TASK_ATACHMENT'),
(1, 'TEST_ATTACHMENT');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `authorization_log`
--

CREATE TABLE `authorization_log` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(32) NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `user_type` tinyint(1) unsigned DEFAULT NULL,
  `time` datetime NOT NULL,
  `user_ip` varchar(15) NOT NULL,
  `proxy_ip` varchar(15) DEFAULT NULL,
  `browser` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `authorization_log_type`
--

CREATE TABLE `authorization_log_type` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `authorization_log_type`
--

INSERT INTO `authorization_log_type` (`id`, `name`) VALUES
(2, 'FAILED_LOGIN'),
(3, 'LOGOUT'),
(1, 'SUCCESSFUL_LOGIN');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `authorization_log_user_type`
--

CREATE TABLE `authorization_log_user_type` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `authorization_log_user_type`
--

INSERT INTO `authorization_log_user_type` (`id`, `name`) VALUES
(1, 'ADMINISTRATOR'),
(2, 'USER');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bug_tracker_jira`
--

CREATE TABLE `bug_tracker_jira` (
  `id` int(10) unsigned NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `project_key` varchar(255) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bug_tracker_mantis`
--

CREATE TABLE `bug_tracker_mantis` (
  `id` int(10) unsigned NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bug_tracker_status`
--

CREATE TABLE `bug_tracker_status` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `bug_tracker_status`
--

INSERT INTO `bug_tracker_status` (`id`, `name`) VALUES
(2, 'ACTIVE'),
(3, 'DELETED'),
(1, 'INACTIVE');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bug_tracker_type`
--

CREATE TABLE `bug_tracker_type` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `bug_tracker_type`
--

INSERT INTO `bug_tracker_type` (`id`, `name`) VALUES
(1, 'INTERNAL'),
(2, 'JIRA'),
(3, 'MANTIS');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comment`
--

CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `subject_type` tinyint(2) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `content` text NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comment_status`
--

CREATE TABLE `comment_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `comment_status`
--

INSERT INTO `comment_status` (`id`, `name`) VALUES
(1, 'ACTIVE'),
(2, 'BLOCEKD'),
(3, 'DELETED');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `comment_subject_type`
--

CREATE TABLE `comment_subject_type` (
  `id` tinyint(2) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `comment_subject_type`
--

INSERT INTO `comment_subject_type` (`id`, `name`) VALUES
(2, 'DEFECT'),
(1, 'TASK');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect`
--

CREATE TABLE `defect` (
  `id` int(10) unsigned NOT NULL,
  `ordinal_no` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `release_id` int(10) unsigned DEFAULT NULL,
  `phase_id` int(10) unsigned DEFAULT NULL,
  `assigner_id` int(10) unsigned NOT NULL,
  `assignee_id` int(10) unsigned NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `priority` tinyint(1) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `author_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Wyzwalacze `defect`
--
DELIMITER $$
CREATE TRIGGER `defect_insert_ordinal_no` BEFORE INSERT ON `defect`
 FOR EACH ROW BEGIN
  DECLARE maxOrdinalNo INT;
  SELECT MAX(t.ordinal_no) INTO maxOrdinalNo FROM defect AS t WHERE t.project_id = NEW.project_id;
  
  IF maxOrdinalNo IS NULL THEN
    SET NEW.ordinal_no = 1;
  ELSE
    SET NEW.ordinal_no = maxOrdinalNo + 1;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect_environment`
--

CREATE TABLE `defect_environment` (
  `defect_id` int(10) unsigned NOT NULL,
  `environment_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect_jira`
--

CREATE TABLE `defect_jira` (
  `id` int(10) unsigned NOT NULL,
  `bug_tracker_id` int(10) unsigned NOT NULL,
  `no` int(10) unsigned NOT NULL,
  `summary` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect_mantis`
--

CREATE TABLE `defect_mantis` (
  `id` int(10) unsigned NOT NULL,
  `bug_tracker_id` int(10) unsigned NOT NULL,
  `no` int(11) NOT NULL,
  `summary` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect_priority`
--

CREATE TABLE `defect_priority` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `defect_priority`
--

INSERT INTO `defect_priority` (`id`, `name`) VALUES
(1, 'BLOCKER'),
(2, 'CRITICAL'),
(3, 'MAJOR'),
(4, 'MINOR'),
(5, 'TRIVIAL');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect_status`
--

CREATE TABLE `defect_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `defect_status`
--

INSERT INTO `defect_status` (`id`, `name`) VALUES
(7, 'FAIL'),
(3, 'FINISHED'),
(5, 'INVALID'),
(2, 'IN_PROGRESS'),
(1, 'OPEN'),
(8, 'REOPEN'),
(4, 'RESOLVED'),
(6, 'SUCCESS');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `defect_version`
--

CREATE TABLE `defect_version` (
  `defect_id` int(10) unsigned NOT NULL,
  `version_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `environment`
--

CREATE TABLE `environment` (
  `id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `exploratory_test`
--

CREATE TABLE `exploratory_test` (
  `id` int(10) unsigned NOT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `duration` int(10) unsigned DEFAULT NULL,
  `test_card` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `file`
--

CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL,
  `is_temporary` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `extension` varchar(5) NOT NULL,
  `path` text NOT NULL,
  `create_date` datetime NOT NULL,
  `remove_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `history`
--

CREATE TABLE `history` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `subject_id` int(10) unsigned NOT NULL,
  `subject_type` tinyint(2) unsigned NOT NULL,
  `type` tinyint(2) unsigned NOT NULL,
  `field1` varchar(255) DEFAULT NULL,
  `field2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `history_subject_type`
--

CREATE TABLE `history_subject_type` (
  `id` tinyint(2) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `history_subject_type`
--

INSERT INTO `history_subject_type` (`id`, `name`) VALUES
(5, 'DEFECT'),
(4, 'EXPLORATORY_TEST'),
(2, 'OTHER_TEST'),
(1, 'TASK'),
(3, 'TEST_CASE');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `history_type`
--

CREATE TABLE `history_type` (
  `id` tinyint(2) unsigned NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `history_type`
--

INSERT INTO `history_type` (`id`, `name`) VALUES
(12, 'ADD_DEFECT_TO_TASK'),
(7, 'ADD_TEST_TO_TASK'),
(10, 'CHANGE_DEFECT'),
(11, 'CHANGE_DEFECT_STATUS'),
(3, 'CHANGE_EXPLORATORY_TEST'),
(1, 'CHANGE_OTHER_TEST'),
(5, 'CHANGE_TASK'),
(6, 'CHANGE_TASK_STATUS'),
(2, 'CHANGE_TEST_CASE'),
(9, 'CREATE_DEFECT'),
(4, 'CREATE_TASK'),
(13, 'DELETE_DEFECT_FROM_TASK'),
(8, 'DELETE_TEST_FROM_TASK');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `message`
--

CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `sender_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `recipient_id` int(10) unsigned NOT NULL,
  `recipient_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `thread_id` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `to_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `from_status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `received_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `message_from_status`
--

CREATE TABLE `message_from_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `message_from_status`
--

INSERT INTO `message_from_status` (`id`, `name`) VALUES
(2, 'DELETED'),
(3, 'DELETED_PERNAMENTLY'),
(1, 'SENT');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `message_status`
--

CREATE TABLE `message_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `message_status`
--

INSERT INTO `message_status` (`id`, `name`) VALUES
(1, 'BASE'),
(3, 'FORWARDED'),
(2, 'REPLIED');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `message_to_status`
--

CREATE TABLE `message_to_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `message_to_status`
--

INSERT INTO `message_to_status` (`id`, `name`) VALUES
(3, 'DELETED'),
(4, 'DELETED_PERNAMENTLY'),
(2, 'READ'),
(1, 'UNREAD');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `message_user_type`
--

CREATE TABLE `message_user_type` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `message_user_type`
--

INSERT INTO `message_user_type` (`id`, `name`) VALUES
(2, 'COACH'),
(1, 'USER');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `phase`
--

CREATE TABLE `phase` (
  `id` int(10) unsigned NOT NULL,
  `release_id` int(10) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(160) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `project`
--

CREATE TABLE `project` (
  `id` int(10) unsigned NOT NULL,
  `prefix` varchar(6) NOT NULL,
  `status` tinyint(2) unsigned NOT NULL,
  `create_date` datetime NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` text NOT NULL,
  `open_status_color` varchar(7) NOT NULL,
  `in_progress_status_color` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `project_bug_tracker`
--

CREATE TABLE `project_bug_tracker` (
  `id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `bug_tracker_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `bug_tracker_type` tinyint(1) unsigned NOT NULL,
  `bug_tracker_status` tinyint(1) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `project_status`
--

CREATE TABLE `project_status` (
  `id` tinyint(2) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `project_status`
--

INSERT INTO `project_status` (`id`, `name`) VALUES
(1, 'ACTIVE'),
(3, 'FINISHED'),
(2, 'SUSPENDED');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `release`
--

CREATE TABLE `release` (
  `id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `name` varchar(64) NOT NULL,
  `description` varchar(160) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `resolution`
--

CREATE TABLE `resolution` (
  `id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `name` varchar(40) NOT NULL,
  `color` varchar(7) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `role`
--

CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `role_action`
--

CREATE TABLE `role_action` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `role_action`
--

INSERT INTO `role_action` (`id`, `name`) VALUES
(1, 'PROJECT_ATTACHMENT'),
(2, 'PROJECT_STATUS'),
(3, 'RELEASE_AND_PHASE_MANAGEMENT'),
(4, 'ENVIRONMENT_ADD'),
(5, 'ENVIRONMENT_MODIFY'),
(6, 'VERSION_ADD'),
(7, 'VERSION_MODIFY'),
(8, 'TEST_ADD'),
(9, 'TEST_EDIT_CREATED_BY_YOU'),
(10, 'TEST_EDIT_ALL'),
(11, 'TASK_ADD'),
(12, 'TASK_EDIT_CREATED_BY_YOU'),
(13, 'TASK_EDIT_ASSIGNED_TO_YOU'),
(14, 'TASK_EDIT_ALL'),
(15, 'TASK_CHANGE_STATUS_CREATED_BY_YOU'),
(16, 'TASK_CHANGE_STATUS_ASSIGNED_TO_YOU'),
(17, 'TASK_CHANGE_STATUS_ALL'),
(18, 'TASK_ASSIGN_CREATED_BY_YOU'),
(19, 'TASK_ASSIGN_ASSIGNED_TO_YOU'),
(20, 'TASK_ASSIGN_ASSIGNED_BY_YOU'),
(21, 'TASK_ASSIGN_ALL'),
(22, 'TASK_ATTACHMENT_CREATED_BY_YOU'),
(23, 'TASK_ATTACHMENT_ASSIGNED_TO_YOU'),
(24, 'TASK_ATTACHMENT_ALL'),
(25, 'TASK_TEST_MODIFY_CREATED_BY_YOU'),
(26, 'TASK_TEST_MODIFY_ASSIGNED_TO_YOU'),
(27, 'TASK_TEST_MODIFY_ALL'),
(28, 'TASK_DEFECT_MODIFY_CREATED_BY_YOU'),
(29, 'TASK_DEFECT_MODIFY_ASSIGNED_TO_YOU'),
(30, 'TASK_DEFECT_MODIFY_ALL'),
(31, 'REPORT_GENERATE'),
(32, 'DEFECT_ADD'),
(33, 'DEFECT_EDIT_CREATED_BY_YOU'),
(34, 'DEFECT_EDIT_ALL');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `role_settings`
--

CREATE TABLE `role_settings` (
  `id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `role_action_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `role_user`
--

CREATE TABLE `role_user` (
  `id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task`
--

CREATE TABLE `task` (
  `id` int(10) unsigned NOT NULL,
  `ordinal_no` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `release_id` int(10) unsigned DEFAULT NULL,
  `phase_id` int(10) unsigned DEFAULT NULL,
  `assigner_id` int(10) unsigned NOT NULL,
  `assignee_id` int(10) unsigned NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `priority` tinyint(1) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `resolution_id` int(10) unsigned DEFAULT NULL,
  `author_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Wyzwalacze `task`
--
DELIMITER $$
CREATE TRIGGER `task_insert_ordinal_no` BEFORE INSERT ON `task`
 FOR EACH ROW BEGIN
  DECLARE maxOrdinalNo INT;
  SELECT MAX(t.ordinal_no) INTO maxOrdinalNo FROM task AS t WHERE t.project_id = NEW.project_id;
  
  IF maxOrdinalNo IS NULL THEN
    SET NEW.ordinal_no = 1;
  ELSE
    SET NEW.ordinal_no = maxOrdinalNo + 1;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task_defect`
--

CREATE TABLE `task_defect` (
  `id` int(11) NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  `defect_id` int(10) unsigned NOT NULL,
  `bug_tracker_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task_environment`
--

CREATE TABLE `task_environment` (
  `task_id` int(10) unsigned NOT NULL,
  `environment_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task_priority`
--

CREATE TABLE `task_priority` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `task_priority`
--

INSERT INTO `task_priority` (`id`, `name`) VALUES
(1, 'CRITICAL'),
(2, 'MAJOR'),
(3, 'MINOR'),
(4, 'TRIVIAL');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task_status`
--

CREATE TABLE `task_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `task_status`
--

INSERT INTO `task_status` (`id`, `name`) VALUES
(3, 'CLOSED'),
(2, 'IN_PROGRESS'),
(1, 'OPEN'),
(4, 'REOPEN');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task_test`
--

CREATE TABLE `task_test` (
  `id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `resolution_id` int(10) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `task_version`
--

CREATE TABLE `task_version` (
  `task_id` int(10) unsigned NOT NULL,
  `version_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `test`
--

CREATE TABLE `test` (
  `id` int(10) unsigned NOT NULL,
  `ordinal_no` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `create_date` datetime NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `family_id` int(10) unsigned DEFAULT NULL,
  `current_version` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Wyzwalacze `test`
--
DELIMITER $$
CREATE TRIGGER `test_insert_ordinal_no` BEFORE INSERT ON `test`
 FOR EACH ROW BEGIN  
  DECLARE maxOrdinalNo INT;
  SELECT MAX(t.ordinal_no) INTO maxOrdinalNo FROM test AS t WHERE t.project_id = NEW.project_id;
  
  IF maxOrdinalNo IS NULL THEN
    SET NEW.ordinal_no = 1;
  ELSE
    SET NEW.ordinal_no = maxOrdinalNo + 1;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `test_case`
--

CREATE TABLE `test_case` (
  `id` int(10) unsigned NOT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `presuppositions` text NOT NULL,
  `result` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `test_status`
--

CREATE TABLE `test_status` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `test_status`
--

INSERT INTO `test_status` (`id`, `name`) VALUES
(1, 'ACTIVE'),
(2, 'DELETED');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `test_type`
--

CREATE TABLE `test_type` (
  `id` tinyint(1) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `test_type`
--

INSERT INTO `test_type` (`id`, `name`) VALUES
(3, 'EXPLORATORY_TEST'),
(1, 'OTHER_TEST'),
(2, 'TEST_CASE');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user`
--

CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `new_email` varchar(255) DEFAULT NULL,
  `password` varchar(40) DEFAULT NULL,
  `salt` varchar(16) DEFAULT NULL,
  `reset_password` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `create_date` datetime NOT NULL,
  `last_login_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `token` varchar(32) DEFAULT NULL,
  `firstname` varchar(32) NOT NULL,
  `lastname` varchar(64) NOT NULL,
  `administrator` tinyint(1) NOT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `default_project_id` int(10) unsigned NOT NULL DEFAULT '0',
  `default_locale` varchar(5) NOT NULL DEFAULT 'pl_PL'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `user`
--

INSERT INTO `user` (`id`, `email`, `new_email`, `password`, `salt`, `reset_password`, `status`, `create_date`, `last_login_date`, `token`, `firstname`, `lastname`, `administrator`, `organization`, `department`, `phone_number`, `default_project_id`, `default_locale`) VALUES
(1, 'administrator@testarena.pl', 'administrator@testarena.pl', '430a263384ed523bf9b7d511a6ce54d1dea14afa', '76d009e195a047e8', 0, 2, '2012-10-02 14:30:00', '2015-10-02 11:59:43', NULL, 'Administrator', 'TestArena', 1, '21 CN', 'IT', '', 12, 'pl_PL');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_status`
--

CREATE TABLE `user_status` (
  `id` tinyint(2) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Zrzut danych tabeli `user_status`
--

INSERT INTO `user_status` (`id`, `name`) VALUES
(2, 'ACTIVE'),
(1, 'INACTIVE');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `version`
--

CREATE TABLE `version` (
  `id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `attachment`
--
ALTER TABLE `attachment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uFileIdSubjectIdType` (`file_id`,`subject_id`,`type`),
  ADD KEY `iFileId` (`file_id`),
  ADD KEY `iType` (`type`);

--
-- Indexes for table `attachment_type`
--
ALTER TABLE `attachment_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `authorization_log`
--
ALTER TABLE `authorization_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iType` (`type`),
  ADD KEY `iUserType` (`user_type`);

--
-- Indexes for table `authorization_log_type`
--
ALTER TABLE `authorization_log_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `authorization_log_user_type`
--
ALTER TABLE `authorization_log_user_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `bug_tracker_jira`
--
ALTER TABLE `bug_tracker_jira`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bug_tracker_mantis`
--
ALTER TABLE `bug_tracker_mantis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bug_tracker_status`
--
ALTER TABLE `bug_tracker_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `bug_tracker_type`
--
ALTER TABLE `bug_tracker_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iSubjectId` (`subject_id`),
  ADD KEY `iStatus` (`status`),
  ADD KEY `iUserId` (`user_id`),
  ADD KEY `iSubjectType` (`subject_type`);

--
-- Indexes for table `comment_status`
--
ALTER TABLE `comment_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `comment_subject_type`
--
ALTER TABLE `comment_subject_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `defect`
--
ALTER TABLE `defect`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iProjectId` (`project_id`),
  ADD KEY `iReleaseId` (`release_id`),
  ADD KEY `iPhaseId` (`phase_id`),
  ADD KEY `iAssignerId` (`assigner_id`),
  ADD KEY `iAssigneeId` (`assignee_id`),
  ADD KEY `iStatus` (`status`),
  ADD KEY `iPriority` (`priority`),
  ADD KEY `fkDefectAuthorId` (`author_id`);

--
-- Indexes for table `defect_environment`
--
ALTER TABLE `defect_environment`
  ADD PRIMARY KEY (`defect_id`,`environment_id`),
  ADD KEY `fkdefectEnvironmentEnvironmentId` (`environment_id`);

--
-- Indexes for table `defect_jira`
--
ALTER TABLE `defect_jira`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uBugTrackerIdNo` (`bug_tracker_id`,`no`),
  ADD KEY `iBugTrackerId` (`bug_tracker_id`);

--
-- Indexes for table `defect_mantis`
--
ALTER TABLE `defect_mantis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uBugTrackerIdNo` (`bug_tracker_id`,`no`),
  ADD KEY `iBugTrackerId` (`bug_tracker_id`);

--
-- Indexes for table `defect_priority`
--
ALTER TABLE `defect_priority`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `defect_status`
--
ALTER TABLE `defect_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `defect_version`
--
ALTER TABLE `defect_version`
  ADD PRIMARY KEY (`defect_id`,`version_id`),
  ADD KEY `fkdefectVersionVersionId` (`version_id`);

--
-- Indexes for table `environment`
--
ALTER TABLE `environment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uProjectIdName` (`project_id`,`name`),
  ADD KEY `iProjectId` (`project_id`);

--
-- Indexes for table `exploratory_test`
--
ALTER TABLE `exploratory_test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iTestId` (`test_id`);

--
-- Indexes for table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iUserId` (`user_id`),
  ADD KEY `iSubjectType` (`subject_type`),
  ADD KEY `iType` (`type`);

--
-- Indexes for table `history_subject_type`
--
ALTER TABLE `history_subject_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `history_type`
--
ALTER TABLE `history_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iSenderId` (`sender_id`),
  ADD KEY `iSenderType` (`sender_type`),
  ADD KEY `iRecipientId` (`recipient_id`),
  ADD KEY `iRecipientType` (`recipient_type`),
  ADD KEY `iThreadId` (`thread_id`),
  ADD KEY `iStatus` (`status`),
  ADD KEY `iToStatus` (`to_status`),
  ADD KEY `iFromStatus` (`from_status`);

--
-- Indexes for table `message_from_status`
--
ALTER TABLE `message_from_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `message_status`
--
ALTER TABLE `message_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `message_to_status`
--
ALTER TABLE `message_to_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `message_user_type`
--
ALTER TABLE `message_user_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `phase`
--
ALTER TABLE `phase`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iReleaseId` (`release_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uPrefix` (`prefix`),
  ADD UNIQUE KEY `uName` (`name`),
  ADD KEY `iStatus` (`status`);

--
-- Indexes for table `project_bug_tracker`
--
ALTER TABLE `project_bug_tracker`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uBugTrackerIdBugTrackerType` (`bug_tracker_id`,`bug_tracker_type`),
  ADD KEY `iProjectId` (`project_id`),
  ADD KEY `iBugTrackerId` (`bug_tracker_id`),
  ADD KEY `iBugTrackerType` (`bug_tracker_type`),
  ADD KEY `iBugTrackerStatus` (`bug_tracker_status`);

--
-- Indexes for table `project_status`
--
ALTER TABLE `project_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `release`
--
ALTER TABLE `release`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iProjectId` (`project_id`);

--
-- Indexes for table `resolution`
--
ALTER TABLE `resolution`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uProjectIdName` (`project_id`,`name`),
  ADD UNIQUE KEY `uProjectIdColor` (`project_id`,`color`),
  ADD KEY `iProjectId` (`project_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iProjectId` (`project_id`);

--
-- Indexes for table `role_action`
--
ALTER TABLE `role_action`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_settings`
--
ALTER TABLE `role_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uRsRoleIdRoleActionId` (`role_id`,`role_action_id`),
  ADD KEY `iRoleId` (`role_id`),
  ADD KEY `iRoleActionId` (`role_action_id`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uRuRoleIdUserId` (`role_id`,`user_id`),
  ADD KEY `iRoleId` (`role_id`),
  ADD KEY `iUserId` (`user_id`);

--
-- Indexes for table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iProjectId` (`project_id`),
  ADD KEY `iReleaseId` (`release_id`),
  ADD KEY `iPhaseId` (`phase_id`),
  ADD KEY `iAssignerId` (`assigner_id`),
  ADD KEY `iAssigneeId` (`assignee_id`),
  ADD KEY `iStatus` (`status`),
  ADD KEY `iPriority` (`priority`),
  ADD KEY `iResolutionId` (`resolution_id`),
  ADD KEY `fkTaskAuthorId` (`author_id`);

--
-- Indexes for table `task_defect`
--
ALTER TABLE `task_defect`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uTaskIdDefectIdBugTrackerId` (`task_id`,`defect_id`,`bug_tracker_id`),
  ADD KEY `iTaskId` (`task_id`),
  ADD KEY `iDefectId` (`defect_id`),
  ADD KEY `iBugTrackerId` (`bug_tracker_id`);

--
-- Indexes for table `task_environment`
--
ALTER TABLE `task_environment`
  ADD PRIMARY KEY (`task_id`,`environment_id`),
  ADD KEY `fkTaskEnvironmentEnvironmentId` (`environment_id`);

--
-- Indexes for table `task_priority`
--
ALTER TABLE `task_priority`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `task_status`
--
ALTER TABLE `task_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `task_test`
--
ALTER TABLE `task_test`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uTaskIdTestId` (`task_id`,`test_id`),
  ADD KEY `iTaskId` (`task_id`),
  ADD KEY `iTestId` (`test_id`),
  ADD KEY `iResolutionId` (`resolution_id`);

--
-- Indexes for table `task_version`
--
ALTER TABLE `task_version`
  ADD PRIMARY KEY (`task_id`,`version_id`),
  ADD KEY `fkTaskVersionVersionId` (`version_id`);

--
-- Indexes for table `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iProjectId` (`project_id`),
  ADD KEY `iStatus` (`status`),
  ADD KEY `iType` (`type`),
  ADD KEY `iAuthorId` (`author_id`),
  ADD KEY `iFamilyId` (`family_id`);

--
-- Indexes for table `test_case`
--
ALTER TABLE `test_case`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iTestId` (`test_id`);

--
-- Indexes for table `test_status`
--
ALTER TABLE `test_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `test_type`
--
ALTER TABLE `test_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uEmail` (`email`),
  ADD KEY `iStatus` (`status`);

--
-- Indexes for table `user_status`
--
ALTER TABLE `user_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uName` (`name`);

--
-- Indexes for table `version`
--
ALTER TABLE `version`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uProjectIdName` (`project_id`,`name`),
  ADD KEY `iProjectId` (`project_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `attachment`
--
ALTER TABLE `attachment`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `authorization_log`
--
ALTER TABLE `authorization_log`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `bug_tracker_jira`
--
ALTER TABLE `bug_tracker_jira`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `bug_tracker_mantis`
--
ALTER TABLE `bug_tracker_mantis`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `defect`
--
ALTER TABLE `defect`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `defect_jira`
--
ALTER TABLE `defect_jira`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `defect_mantis`
--
ALTER TABLE `defect_mantis`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `environment`
--
ALTER TABLE `environment`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `exploratory_test`
--
ALTER TABLE `exploratory_test`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `file`
--
ALTER TABLE `file`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `history`
--
ALTER TABLE `history`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `message`
--
ALTER TABLE `message`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `phase`
--
ALTER TABLE `phase`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `project`
--
ALTER TABLE `project`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `project_bug_tracker`
--
ALTER TABLE `project_bug_tracker`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `release`
--
ALTER TABLE `release`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `resolution`
--
ALTER TABLE `resolution`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `role`
--
ALTER TABLE `role`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `role_settings`
--
ALTER TABLE `role_settings`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `task`
--
ALTER TABLE `task`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `task_defect`
--
ALTER TABLE `task_defect`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `task_test`
--
ALTER TABLE `task_test`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `test`
--
ALTER TABLE `test`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `test_case`
--
ALTER TABLE `test_case`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT dla tabeli `version`
--
ALTER TABLE `version`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `attachment`
--
ALTER TABLE `attachment`
  ADD CONSTRAINT `fkAttachmentFileId` FOREIGN KEY (`file_id`) REFERENCES `file` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkAttachmentType` FOREIGN KEY (`type`) REFERENCES `attachment_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `authorization_log`
--
ALTER TABLE `authorization_log`
  ADD CONSTRAINT `fkAuthorizationLogType` FOREIGN KEY (`type`) REFERENCES `authorization_log_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkAuthorizationLogUserType` FOREIGN KEY (`user_type`) REFERENCES `authorization_log_user_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `fkCommentStatus` FOREIGN KEY (`status`) REFERENCES `comment_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkCommentUserId` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `defect`
--
ALTER TABLE `defect`
  ADD CONSTRAINT `fkDefectAssigneeId` FOREIGN KEY (`assignee_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectAssignerId` FOREIGN KEY (`assigner_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectAuthorId` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectPhaseId` FOREIGN KEY (`phase_id`) REFERENCES `phase` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectPriority` FOREIGN KEY (`priority`) REFERENCES `defect_priority` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectReleaseId` FOREIGN KEY (`release_id`) REFERENCES `release` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkDefectStatus` FOREIGN KEY (`status`) REFERENCES `defect_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `defect_environment`
--
ALTER TABLE `defect_environment`
  ADD CONSTRAINT `fkdefectEnvironmentDefectId` FOREIGN KEY (`defect_id`) REFERENCES `defect` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkdefectEnvironmentEnvironmentId` FOREIGN KEY (`environment_id`) REFERENCES `environment` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `defect_jira`
--
ALTER TABLE `defect_jira`
  ADD CONSTRAINT `fkDefectJiraBugTrackerId` FOREIGN KEY (`bug_tracker_id`) REFERENCES `bug_tracker_jira` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `defect_mantis`
--
ALTER TABLE `defect_mantis`
  ADD CONSTRAINT `fkDefectMantisBugTrackerId` FOREIGN KEY (`bug_tracker_id`) REFERENCES `bug_tracker_mantis` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `defect_version`
--
ALTER TABLE `defect_version`
  ADD CONSTRAINT `fkdefectVersionDefectId` FOREIGN KEY (`defect_id`) REFERENCES `defect` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkdefectVersionVersionId` FOREIGN KEY (`version_id`) REFERENCES `version` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `environment`
--
ALTER TABLE `environment`
  ADD CONSTRAINT `fkEnvironmentProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `exploratory_test`
--
ALTER TABLE `exploratory_test`
  ADD CONSTRAINT `fkExploratoryTestTestId` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `fkHistorySubjectType` FOREIGN KEY (`subject_type`) REFERENCES `history_subject_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkHistoryType` FOREIGN KEY (`type`) REFERENCES `history_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkHistoryUserId` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fkMessageFromStatus` FOREIGN KEY (`from_status`) REFERENCES `message_from_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkMessageRecipientType` FOREIGN KEY (`recipient_type`) REFERENCES `message_user_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkMessageSenderType` FOREIGN KEY (`sender_type`) REFERENCES `message_user_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkMessageStatus` FOREIGN KEY (`status`) REFERENCES `message_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkMessageToStatus` FOREIGN KEY (`to_status`) REFERENCES `message_to_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `phase`
--
ALTER TABLE `phase`
  ADD CONSTRAINT `fkPhaseReleaseId` FOREIGN KEY (`release_id`) REFERENCES `release` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `fkProjectStatus` FOREIGN KEY (`status`) REFERENCES `project_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `project_bug_tracker`
--
ALTER TABLE `project_bug_tracker`
  ADD CONSTRAINT `fkProjectBugTrackerBugTrackerStatus` FOREIGN KEY (`bug_tracker_status`) REFERENCES `bug_tracker_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkProjectBugTrackerBugTrackerType` FOREIGN KEY (`bug_tracker_type`) REFERENCES `bug_tracker_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkProjectBugTrackerProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `release`
--
ALTER TABLE `release`
  ADD CONSTRAINT `fkReleaseProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `resolution`
--
ALTER TABLE `resolution`
  ADD CONSTRAINT `fkResolutionProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `role`
--
ALTER TABLE `role`
  ADD CONSTRAINT `fkRoleProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `role_settings`
--
ALTER TABLE `role_settings`
  ADD CONSTRAINT `fkRoleSettingsRoleActionId` FOREIGN KEY (`role_action_id`) REFERENCES `role_action` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkRoleSettingsRoleId` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `fkRoleUserRoleId` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkRoleUserUserId` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `fkTaskAssigneeId` FOREIGN KEY (`assignee_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskAssignerId` FOREIGN KEY (`assigner_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskAuthorId` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskPhaseId` FOREIGN KEY (`phase_id`) REFERENCES `phase` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskPriority` FOREIGN KEY (`priority`) REFERENCES `task_priority` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskReleaseId` FOREIGN KEY (`release_id`) REFERENCES `release` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskResolutionId` FOREIGN KEY (`resolution_id`) REFERENCES `resolution` (`id`),
  ADD CONSTRAINT `fkTaskStatus` FOREIGN KEY (`status`) REFERENCES `task_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `task_defect`
--
ALTER TABLE `task_defect`
  ADD CONSTRAINT `fkTaskDefectTaskId` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `task_environment`
--
ALTER TABLE `task_environment`
  ADD CONSTRAINT `fkTaskEnvironmentEnvironmentId` FOREIGN KEY (`environment_id`) REFERENCES `environment` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskEnvironmentTaskId` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `task_test`
--
ALTER TABLE `task_test`
  ADD CONSTRAINT `fkTaskTestResolutionId` FOREIGN KEY (`resolution_id`) REFERENCES `resolution` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskTestTaskId` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskTestTestId` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `task_version`
--
ALTER TABLE `task_version`
  ADD CONSTRAINT `fkTaskVersionTaskId` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTaskVersionVersionId` FOREIGN KEY (`version_id`) REFERENCES `version` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `fkTestAuhorId` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTestProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTestStatus` FOREIGN KEY (`status`) REFERENCES `test_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fkTestType` FOREIGN KEY (`type`) REFERENCES `test_type` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `test_case`
--
ALTER TABLE `test_case`
  ADD CONSTRAINT `fkTestCaseTestId` FOREIGN KEY (`test_id`) REFERENCES `test` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fkUserStatus` FOREIGN KEY (`status`) REFERENCES `user_status` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Ograniczenia dla tabeli `version`
--
ALTER TABLE `version`
  ADD CONSTRAINT `fkVersionProjectId` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
