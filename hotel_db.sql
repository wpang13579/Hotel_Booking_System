-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 13, 2024 at 02:05 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `guest`
--

CREATE TABLE `guest` (
  `guest_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `guest_dob` date NOT NULL,
  `guest_phone` int(11) NOT NULL,
  `guest_address` varchar(255) NOT NULL,
  `gender` enum('male','female','','') NOT NULL,
  `record_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest`
--

INSERT INTO `guest` (`guest_id`, `guest_name`, `guest_email`, `guest_dob`, `guest_phone`, `guest_address`, `gender`, `record_date`) VALUES
(1, 'guest1', 'guest1@gmail.com', '2024-12-13', 111111111, 'qwer', 'male', '2024-12-13'),
(2, 'yw', 'yw@gmail.com', '2024-10-30', 1111111111, 'twtwwt', 'male', '2024-12-13');

-- --------------------------------------------------------

--
-- Table structure for table `guest_revenue`
--

CREATE TABLE `guest_revenue` (
  `revenue_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `occur_date` datetime NOT NULL,
  `stay_id` int(11) NOT NULL,
  `redemption_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guest_revenue`
--

INSERT INTO `guest_revenue` (`revenue_id`, `amount`, `occur_date`, `stay_id`, `redemption_id`) VALUES
(1, 4000, '2024-12-13 14:56:00', 7, 1),
(2, 3600, '2024-12-13 14:57:00', 8, 2),
(3, 4000, '2024-12-13 15:18:00', 9, 3),
(4, 4000, '2024-12-13 15:26:00', 10, 4);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_assign`
--

CREATE TABLE `inventory_assign` (
  `id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_assign`
--

INSERT INTO `inventory_assign` (`id`, `room_type_id`, `inventory_id`, `quantity`) VALUES
(2, 1, 1, 1),
(3, 1, 2, 1),
(4, 1, 4, 1),
(5, 1, 6, 1),
(6, 1, 9, 1),
(8, 2, 1, 3),
(9, 2, 2, 1),
(10, 2, 4, 2),
(11, 2, 6, 1),
(12, 2, 8, 2),
(13, 2, 9, 2),
(14, 2, 10, 2),
(15, 3, 1, 4),
(16, 3, 2, 1),
(17, 3, 3, 2),
(18, 3, 4, 4),
(19, 3, 5, 1),
(20, 3, 7, 2),
(21, 3, 8, 2),
(22, 3, 9, 2),
(23, 3, 10, 2),
(24, 3, 11, 2),
(25, 3, 10, 2);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_management`
--

CREATE TABLE `inventory_management` (
  `id` int(11) NOT NULL,
  `inv_name` varchar(100) NOT NULL,
  `inv_price` decimal(10,2) NOT NULL,
  `inv_quantity` int(11) NOT NULL,
  `inv_category` enum('linens','toiletries','complimentary items') NOT NULL,
  `alert_level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_management`
--

INSERT INTO `inventory_management` (`id`, `inv_name`, `inv_price`, `inv_quantity`, `inv_category`, `alert_level`) VALUES
(1, 'pillow', 10.00, 10, 'linens', 10),
(2, 'blanket', 10.00, 8, 'linens', 10),
(3, 'slipper', 10.00, 8, 'linens', 10),
(4, 'towels', 10.00, 8, 'toiletries', 10),
(5, 'shampoo', 10.00, 20, 'toiletries', 10),
(6, 'bodywash', 10.00, 12, 'toiletries', 10),
(7, 'bath bomb', 10.00, 20, 'toiletries', 10),
(8, 'toothbrush', 10.00, 20, 'toiletries', 10),
(9, 'mineral water', 10.00, 12, 'complimentary items', 10),
(10, 'instant coffee', 10.00, 20, 'complimentary items', 10),
(11, 'wine', 10.00, 20, 'complimentary items', 10),
(25, 'test1', 10.00, 100, 'toiletries', 9);

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_program`
--

CREATE TABLE `loyalty_program` (
  `loyalty_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `tier_level` enum('silver','gold','platinum','bronze') NOT NULL,
  `total_point_redeem` int(11) NOT NULL,
  `total_book_days` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_program`
--

INSERT INTO `loyalty_program` (`loyalty_id`, `points`, `tier_level`, `total_point_redeem`, `total_book_days`, `guest_id`) VALUES
(1, 320, 'bronze', 0, 8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_request`
--

CREATE TABLE `maintenance_request` (
  `req_id` int(11) NOT NULL,
  `req_date` datetime NOT NULL,
  `req_desc` varchar(255) NOT NULL,
  `priority_level` enum('high','medium','low') NOT NULL,
  `req_status` enum('pending','approved') NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_request`
--

INSERT INTO `maintenance_request` (`req_id`, `req_date`, `req_desc`, `priority_level`, `req_status`, `staff_id`, `room_id`) VALUES
(968, '2024-12-12 01:32:27', 'Emergency: pipe leaking', 'high', 'approved', 3, 1),
(969, '2024-12-19 08:33:00', 'repair furniture bed', 'low', 'approved', 3, 3),
(970, '2024-12-12 08:57:00', 'furniture rosak', 'low', 'approved', 8, 4),
(971, '2025-02-28 09:17:00', 'fix lamp', 'low', 'approved', 8, 5),
(972, '2025-05-01 13:45:00', 'desk lamp recheck', 'low', 'approved', 8, 8),
(973, '2024-12-12 16:32:35', 'Emergency: toilet bowl pecah', 'high', 'approved', 3, 6),
(974, '2024-12-12 16:38:26', 'Emergency: tray rosak', 'high', 'approved', 3, 7),
(976, '2024-12-26 23:42:00', 'preventive main for furniture', 'medium', 'approved', 8, 1),
(977, '2024-12-13 07:58:58', 'Emergency: sdasda', 'high', 'approved', 1, 7),
(978, '2024-12-13 08:03:01', 'Emergency: pipe leaking', 'high', 'approved', 3, 1),
(979, '2025-02-20 15:07:00', 'fesdfdsf', 'medium', 'approved', 8, 2),
(980, '2024-12-13 08:20:07', 'Emergency: ddvd', 'high', 'approved', 1, 11),
(981, '2024-12-13 08:27:14', 'Emergency: ddddd', 'high', 'approved', 1, 12),
(982, '2024-12-13 10:20:35', 'Emergency: sadsad', 'high', 'approved', 3, 14);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_task`
--

CREATE TABLE `maintenance_task` (
  `task_id` int(11) NOT NULL,
  `task_status` enum('pending','in progress','completed') NOT NULL,
  `main_type` enum('AC repair','plumbing','lighting','furniture') NOT NULL,
  `task_desc` varchar(255) NOT NULL,
  `completion_date` datetime DEFAULT NULL,
  `req_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_task`
--

INSERT INTO `maintenance_task` (`task_id`, `task_status`, `main_type`, `task_desc`, `completion_date`, `req_id`, `staff_id`) VALUES
(21, 'completed', 'plumbing', 'Emergency: stop cock rosak', '2024-12-19 09:16:00', 968, 4),
(22, 'completed', 'furniture', 'bed rosak', '2024-12-13 09:17:00', 969, 4),
(23, 'in progress', 'lighting', 'change the lamp bulb', NULL, 972, 4),
(24, 'in progress', 'plumbing', 'Emergency: change the toilet bowl to big toilet bowl', NULL, 973, 4),
(25, 'in progress', 'furniture', 'Emergency: change the 2x2 tray', NULL, 974, 4),
(26, 'in progress', 'furniture', 'repair cupboard', NULL, 976, 4),
(27, 'completed', 'plumbing', 'Emergency: change the stopclock for tap', '2024-12-20 13:06:00', 978, 13),
(28, 'in progress', 'AC repair', 'Emergency: assda', NULL, 977, 13),
(29, 'completed', 'lighting', 'fddsfsdf', '2025-03-14 15:08:00', 979, 13),
(30, 'in progress', 'plumbing', 'Emergency: sddda', NULL, 980, 4),
(31, 'in progress', 'plumbing', 'Emergency: fsdfds', NULL, 981, 13),
(32, 'in progress', 'AC repair', 'Emergency: dsfdsf', NULL, 982, 3);

-- --------------------------------------------------------

--
-- Table structure for table `order_inventory`
--

CREATE TABLE `order_inventory` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `contributed_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_inventory`
--

INSERT INTO `order_inventory` (`id`, `order_id`, `inventory_id`, `contributed_quantity`) VALUES
(17, 23, 25, 100);

-- --------------------------------------------------------

--
-- Table structure for table `order_management`
--

CREATE TABLE `order_management` (
  `id` int(11) NOT NULL,
  `o_name` varchar(255) NOT NULL,
  `o_quantity` int(11) NOT NULL,
  `o_status` enum('pending','confirmed','cancelled','added') NOT NULL,
  `o_date` date DEFAULT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_contact` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_management`
--

INSERT INTO `order_management` (`id`, `o_name`, `o_quantity`, `o_status`, `o_date`, `supplier_name`, `supplier_contact`) VALUES
(1, 'pilow', 100, 'added', '2024-12-06', 'tan', '60198624516'),
(2, 'blanket', 100, 'added', '2024-12-06', 'tan', '60198624516'),
(3, 'slipper', 200, 'added', '2024-12-06', 'tan', '60198624516'),
(4, 'towels', 100, 'added', '2024-12-06', 'lau', '0198624516'),
(5, 'shampoo', 100, 'added', '2024-12-06', 'lau', '0198624516'),
(6, 'bodywash', 100, 'added', '2024-12-06', 'lau', '0198624516'),
(7, 'bath bomb', 100, 'added', '2024-12-06', 'lau', '0198624516'),
(8, 'toothbrush', 100, 'added', '2024-12-06', 'lau', '0198624516'),
(9, 'mineral water', 100, 'added', '2024-12-06', 'yw', '4985621435'),
(10, 'instant coffee', 100, 'added', '2024-12-06', 'yw', '4985621435'),
(11, 'wine', 100, 'added', '2024-12-06', 'yw', '4985621435'),
(23, 'test1', 100, 'added', '2024-12-09', 'tan', '60198624516'),
(24, 'test2', 200, 'confirmed', '2024-12-13', 'tan', '0198624516');

-- --------------------------------------------------------

--
-- Table structure for table `redemption_record`
--

CREATE TABLE `redemption_record` (
  `redemption_id` int(11) NOT NULL,
  `redeem_date` int(11) NOT NULL,
  `point_used` int(11) NOT NULL,
  `tier` enum('bronze','silver','gold','platinum') NOT NULL,
  `reward_type` enum('upgrade_room','discount','','') NOT NULL,
  `guest_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `reward_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `redemption_record`
--

INSERT INTO `redemption_record` (`redemption_id`, `redeem_date`, `point_used`, `tier`, `reward_type`, `guest_id`, `reward_id`, `reward_name`) VALUES
(1, 2024, 0, 'bronze', 'upgrade_room', 2, 2, 'Upgrade room'),
(2, 2024, 0, 'bronze', 'discount', 2, 3, 'Discount'),
(3, 2024, 0, 'bronze', 'upgrade_room', 2, 2, 'Upgrade room'),
(4, 2024, 0, 'bronze', 'upgrade_room', 2, 2, 'Upgrade room');

-- --------------------------------------------------------

--
-- Table structure for table `reward`
--

CREATE TABLE `reward` (
  `reward_id` int(11) NOT NULL,
  `reward_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `points_required` int(11) NOT NULL,
  `reward_type` varchar(255) NOT NULL,
  `tier_required` enum('bronze','sliver','gold','platinium') NOT NULL,
  `discount_rate` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward`
--

INSERT INTO `reward` (`reward_id`, `reward_name`, `description`, `points_required`, `reward_type`, `tier_required`, `discount_rate`) VALUES
(1, 'No_rewards', 'no rewards', 0, 'nope', 'bronze', 0),
(2, 'Upgrade room', 'dsda', 0, 'upgrade_room', 'bronze', 0),
(3, 'Discount', 'ddssd', 0, 'discount', 'bronze', 10);

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `room_id` int(11) NOT NULL,
  `room_type` int(11) NOT NULL,
  `room_status` enum('available','occupied','under maintenance','housekeeping','emergency maintenance') NOT NULL,
  `room_price` decimal(10,2) NOT NULL,
  `room_num` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`room_id`, `room_type`, `room_status`, `room_price`, `room_num`) VALUES
(1, 1, 'available', 300.00, 'R001'),
(2, 1, 'housekeeping', 300.00, 'R002'),
(3, 1, 'available', 300.00, 'R003'),
(4, 1, 'available', 300.00, 'R004'),
(5, 1, 'available', 300.00, 'R005'),
(6, 2, 'housekeeping', 500.00, 'R006'),
(7, 2, 'available', 500.00, 'R007'),
(8, 2, 'available', 500.00, 'R008'),
(9, 2, 'available', 500.00, 'R009'),
(10, 2, 'available', 500.00, 'R010'),
(11, 3, 'available', 1000.00, 'R011'),
(12, 3, 'emergency maintenance', 1000.00, 'R012'),
(13, 3, 'available', 1000.00, 'R013'),
(14, 3, 'emergency maintenance', 1000.00, 'R014'),
(15, 3, 'occupied', 1000.00, 'R015');

-- --------------------------------------------------------

--
-- Table structure for table `room_type`
--

CREATE TABLE `room_type` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_type`
--

INSERT INTO `room_type` (`id`, `type`) VALUES
(1, 'superior'),
(2, 'deluxe'),
(3, 'luxury');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `staff_firstname` varchar(50) NOT NULL,
  `staff_lastname` varchar(50) NOT NULL,
  `staff_dob` date NOT NULL,
  `staff_gender` enum('male','female') NOT NULL,
  `staff_contactnum` varchar(50) NOT NULL,
  `staff_email` varchar(50) NOT NULL,
  `staff_hiredate` date NOT NULL,
  `staff_password` varchar(255) NOT NULL,
  `staff_salary` decimal(10,2) NOT NULL,
  `role_id` int(11) NOT NULL,
  `performance_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `staff_firstname`, `staff_lastname`, `staff_dob`, `staff_gender`, `staff_contactnum`, `staff_email`, `staff_hiredate`, `staff_password`, `staff_salary`, `role_id`, `performance_id`) VALUES
(1, 'Bawang', 'Chagee', '1989-08-15', 'female', '011-8965235', 'bawang@gmail.com', '2024-12-07', 'chagee', 4500.00, 3, 27),
(2, 'John', 'Cina', '1982-01-15', 'male', '011-42385574', 'john@gmail.com', '2024-12-07', 'johncena', 4500.00, 4, 29),
(3, 'Tim', 'Cook', '2024-12-03', 'male', '014-8965326', 'tim@gmail.com', '2024-12-07', 'cook', 3500.00, 6, 33),
(4, 'Yilong', 'Ma', '2024-12-03', 'male', '014-8965236', 'elon@gmail.com', '2024-12-07', 'musk', 3000.00, 6, 34),
(5, 'Jeremy', 'Lim', '2002-05-17', 'male', '014-73589654', 'admin@gmail.com', '2024-12-03', 'jeremy', 8000.00, 2, 25),
(6, 'Jake', 'Paul', '2024-12-02', 'male', '014-8652356', 'jake@gmail.com', '2024-12-10', '456', 5000.00, 1, 23),
(7, 'Mike', 'Tyson', '1994-12-08', 'male', '017-8954369', 'mike@gmail.com', '2024-12-11', '789', 5000.00, 1, 24),
(8, 'YW', 'Lee', '2000-01-07', 'male', '014-7654325', 'yw@gmail.com', '2024-12-11', 'ywlee', 3000.00, 4, NULL),
(9, 'Wp', 'Tan', '2002-05-17', 'male', '017-8954516', 'wp@gmail.com', '2024-12-11', 'wptan', 3000.00, 5, 31),
(10, 'YT', 'Low', '2003-03-17', 'male', '017-8953516', 'yt@hotmail.com', '2024-12-11', 'ytlow', 4500.00, 3, 28),
(11, 'Ju', 'Jing Yi', '2003-05-17', 'female', '017-8953516', 'jingyi@gmail.com', '2024-12-11', 'jingyi', 4500.00, 5, NULL),
(12, 'Karina', 'JiMin', '2000-04-11', 'female', '017-8954513', 'karina@gmail.com', '2024-12-11', 'aespakarina', 5000.00, 2, 35),
(13, 'Victor', 'Lim', '2014-12-11', 'male', '01111465931', 'vic@gmail.com', '2023-12-07', '1234', 2000.00, 6, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_performance`
--

CREATE TABLE `staff_performance` (
  `performance_id` int(11) NOT NULL,
  `perf_rating` int(11) NOT NULL,
  `eval_date` date NOT NULL,
  `eval_time` time NOT NULL,
  `perf_comment` varchar(100) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_performance`
--

INSERT INTO `staff_performance` (`performance_id`, `perf_rating`, `eval_date`, `eval_time`, `perf_comment`, `staff_id`) VALUES
(15, 2, '2024-12-11', '01:53:03', 'bad', 6),
(17, 3, '2024-12-11', '02:24:55', 'well', 5),
(18, 4, '2024-12-11', '02:26:06', 'okay', 3),
(20, 5, '2024-12-11', '11:01:58', 'Excellent', 7),
(21, 3, '2024-12-11', '11:17:32', 'Good', 7),
(22, 3, '2024-12-11', '11:18:51', 'Keep it up', 1),
(23, 4, '2024-12-11', '11:45:40', 'Jake vs Mike come come', 6),
(24, 3, '2024-12-09', '11:45:59', 'Why you lose', 7),
(25, 5, '2024-12-11', '11:46:17', 'Well Done Bro', 5),
(26, 5, '2024-12-08', '11:46:34', 'Keep it up!', 12),
(27, 4, '2024-12-11', '11:46:58', 'Please make new drink', 1),
(28, 5, '2024-12-11', '11:47:19', 'Perfect teammates', 10),
(29, 4, '2024-12-11', '11:47:49', 'Okay John', 2),
(31, 5, '2024-12-11', '11:48:23', 'Very good teammate. YYDS', 9),
(33, 4, '2024-12-11', '11:49:00', 'Please release iPhone17', 3),
(34, 4, '2024-12-11', '11:49:24', 'Bravo Yilong!', 4),
(35, 4, '2024-12-11', '11:50:22', 'Good Job!', 12);

-- --------------------------------------------------------

--
-- Table structure for table `staff_role`
--

CREATE TABLE `staff_role` (
  `role_id` int(11) NOT NULL,
  `role_name` enum('admin','HR','guest_manager','room_manager','inventory_manager','normal_staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_role`
--

INSERT INTO `staff_role` (`role_id`, `role_name`) VALUES
(1, 'HR'),
(2, 'admin'),
(3, 'guest_manager'),
(4, 'room_manager'),
(5, 'inventory_manager'),
(6, 'normal_staff');

-- --------------------------------------------------------

--
-- Table structure for table `staff_shift`
--

CREATE TABLE `staff_shift` (
  `shift_id` int(11) NOT NULL,
  `shift_type` enum('morning_shift','night_shift') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_shift`
--

INSERT INTO `staff_shift` (`shift_id`, `shift_type`, `start_time`, `end_time`, `staff_id`) VALUES
(1, 'night_shift', '00:00:00', '08:00:00', 3),
(6, 'morning_shift', '08:00:00', '00:00:00', 6),
(12, 'night_shift', '00:00:00', '08:00:00', 7),
(13, 'night_shift', '00:00:00', '08:00:00', 5),
(14, 'morning_shift', '08:00:00', '00:00:00', 12),
(15, 'morning_shift', '08:00:00', '00:00:00', 1),
(16, 'night_shift', '00:00:00', '08:00:00', 10),
(17, 'morning_shift', '08:00:00', '00:00:00', 2),
(18, 'night_shift', '00:00:00', '08:00:00', 8),
(19, 'morning_shift', '08:00:00', '00:00:00', 9),
(20, 'night_shift', '00:00:00', '08:00:00', 11),
(21, 'morning_shift', '08:00:00', '00:00:00', 13);

-- --------------------------------------------------------

--
-- Table structure for table `staff_shift_swap`
--

CREATE TABLE `staff_shift_swap` (
  `request_id` int(11) NOT NULL,
  `request_date` date NOT NULL,
  `request_status` enum('Pending','Approved','Rejected','') NOT NULL,
  `approval_date` date DEFAULT NULL,
  `request_staff_id` int(11) NOT NULL,
  `target_staff_id` int(11) NOT NULL,
  `approval_staff_id` int(11) DEFAULT NULL,
  `reject_comment` text DEFAULT NULL,
  `request_shift_id` int(11) NOT NULL,
  `target_shift_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_shift_swap`
--

INSERT INTO `staff_shift_swap` (`request_id`, `request_date`, `request_status`, `approval_date`, `request_staff_id`, `target_staff_id`, `approval_staff_id`, `reject_comment`, `request_shift_id`, `target_shift_id`) VALUES
(10, '2024-12-11', 'Approved', '2024-12-11', 6, 7, 6, '', 6, 12),
(11, '2024-12-13', 'Approved', '2024-12-13', 5, 12, 5, '', 13, 14);

-- --------------------------------------------------------

--
-- Table structure for table `stay`
--

CREATE TABLE `stay` (
  `stay_id` int(11) NOT NULL,
  `check_in_date` datetime NOT NULL,
  `check_out_date` datetime NOT NULL,
  `room_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `guest_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stay`
--

INSERT INTO `stay` (`stay_id`, `check_in_date`, `check_out_date`, `room_id`, `staff_id`, `guest_id`) VALUES
(1, '2024-12-01 14:30:29', '2024-12-03 14:30:29', 1, 1, 1),
(2, '2024-12-04 14:30:54', '2024-12-05 14:30:54', 2, 2, 1),
(3, '2024-12-05 14:31:16', '2024-12-07 14:31:00', 6, 1, 1),
(4, '2024-12-09 14:31:42', '2024-12-11 14:31:00', 11, 1, 1),
(5, '2024-12-13 14:32:01', '2024-12-15 14:32:01', 15, 2, 1),
(6, '2024-12-13 14:32:17', '2024-12-15 14:32:17', 14, 1, 1),
(7, '2024-12-13 14:56:00', '2024-12-07 14:31:00', 6, 1, 2),
(8, '2024-12-13 14:57:00', '2024-12-21 14:57:00', 7, 1, 2),
(9, '2024-12-13 15:18:00', '2024-12-11 14:31:00', 11, 1, 2),
(10, '2024-12-13 15:26:00', '2024-12-21 15:26:00', 12, 1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guest`
--
ALTER TABLE `guest`
  ADD PRIMARY KEY (`guest_id`);

--
-- Indexes for table `guest_revenue`
--
ALTER TABLE `guest_revenue`
  ADD PRIMARY KEY (`revenue_id`),
  ADD KEY `redemption_id` (`redemption_id`),
  ADD KEY `stay_id` (`stay_id`);

--
-- Indexes for table `inventory_assign`
--
ALTER TABLE `inventory_assign`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_type_id` (`room_type_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `inventory_management`
--
ALTER TABLE `inventory_management`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  ADD PRIMARY KEY (`loyalty_id`),
  ADD KEY `guest_id` (`guest_id`);

--
-- Indexes for table `maintenance_request`
--
ALTER TABLE `maintenance_request`
  ADD PRIMARY KEY (`req_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `maintenance_task`
--
ALTER TABLE `maintenance_task`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `req_id` (`req_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `order_inventory`
--
ALTER TABLE `order_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_inventory_ibfk_1` (`inventory_id`),
  ADD KEY `order_inventory_ibfk_2` (`order_id`);

--
-- Indexes for table `order_management`
--
ALTER TABLE `order_management`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `redemption_record`
--
ALTER TABLE `redemption_record`
  ADD PRIMARY KEY (`redemption_id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `reward_id` (`reward_id`);

--
-- Indexes for table `reward`
--
ALTER TABLE `reward`
  ADD PRIMARY KEY (`reward_id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `type` (`room_type`);

--
-- Indexes for table `room_type`
--
ALTER TABLE `room_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD KEY `staff_ibfk_2` (`performance_id`),
  ADD KEY `staff_ibfk_1` (`role_id`);

--
-- Indexes for table `staff_performance`
--
ALTER TABLE `staff_performance`
  ADD PRIMARY KEY (`performance_id`),
  ADD KEY `staff_performance_ibfk_1` (`staff_id`);

--
-- Indexes for table `staff_role`
--
ALTER TABLE `staff_role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `staff_shift`
--
ALTER TABLE `staff_shift`
  ADD PRIMARY KEY (`shift_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `staff_shift_swap`
--
ALTER TABLE `staff_shift_swap`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `request_staff_id` (`request_staff_id`),
  ADD KEY `target_staff_id` (`target_staff_id`),
  ADD KEY `request_shift_id` (`request_shift_id`),
  ADD KEY `target_shift_id` (`target_shift_id`),
  ADD KEY `approval_staff_id` (`approval_staff_id`);

--
-- Indexes for table `stay`
--
ALTER TABLE `stay`
  ADD PRIMARY KEY (`stay_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guest`
--
ALTER TABLE `guest`
  MODIFY `guest_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guest_revenue`
--
ALTER TABLE `guest_revenue`
  MODIFY `revenue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_assign`
--
ALTER TABLE `inventory_assign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `inventory_management`
--
ALTER TABLE `inventory_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  MODIFY `loyalty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `maintenance_request`
--
ALTER TABLE `maintenance_request`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=983;

--
-- AUTO_INCREMENT for table `maintenance_task`
--
ALTER TABLE `maintenance_task`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_inventory`
--
ALTER TABLE `order_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_management`
--
ALTER TABLE `order_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `redemption_record`
--
ALTER TABLE `redemption_record`
  MODIFY `redemption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reward`
--
ALTER TABLE `reward`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `room_type`
--
ALTER TABLE `room_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `staff_performance`
--
ALTER TABLE `staff_performance`
  MODIFY `performance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `staff_role`
--
ALTER TABLE `staff_role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `staff_shift`
--
ALTER TABLE `staff_shift`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `staff_shift_swap`
--
ALTER TABLE `staff_shift_swap`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stay`
--
ALTER TABLE `stay`
  MODIFY `stay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guest_revenue`
--
ALTER TABLE `guest_revenue`
  ADD CONSTRAINT `guest_revenue_ibfk_1` FOREIGN KEY (`redemption_id`) REFERENCES `redemption_record` (`redemption_id`),
  ADD CONSTRAINT `guest_revenue_ibfk_2` FOREIGN KEY (`stay_id`) REFERENCES `stay` (`stay_id`);

--
-- Constraints for table `inventory_assign`
--
ALTER TABLE `inventory_assign`
  ADD CONSTRAINT `inventory_assign_ibfk_1` FOREIGN KEY (`room_type_id`) REFERENCES `room_type` (`id`),
  ADD CONSTRAINT `inventory_assign_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_management` (`id`);

--
-- Constraints for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  ADD CONSTRAINT `loyalty_program_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guest` (`guest_id`);

--
-- Constraints for table `maintenance_request`
--
ALTER TABLE `maintenance_request`
  ADD CONSTRAINT `maintenance_request_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`),
  ADD CONSTRAINT `maintenance_request_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `maintenance_task`
--
ALTER TABLE `maintenance_task`
  ADD CONSTRAINT `maintenance_task_ibfk_1` FOREIGN KEY (`req_id`) REFERENCES `maintenance_request` (`req_id`),
  ADD CONSTRAINT `maintenance_task_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_inventory`
--
ALTER TABLE `order_inventory`
  ADD CONSTRAINT `order_inventory_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_management` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_inventory_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `order_management` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `redemption_record`
--
ALTER TABLE `redemption_record`
  ADD CONSTRAINT `redemption_record_ibfk_1` FOREIGN KEY (`guest_id`) REFERENCES `guest` (`guest_id`),
  ADD CONSTRAINT `redemption_record_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `reward` (`reward_id`);

--
-- Constraints for table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `room_ibfk_1` FOREIGN KEY (`room_type`) REFERENCES `room_type` (`id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `staff_role` (`role_id`),
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`performance_id`) REFERENCES `staff_performance` (`performance_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_performance`
--
ALTER TABLE `staff_performance`
  ADD CONSTRAINT `staff_performance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_shift`
--
ALTER TABLE `staff_shift`
  ADD CONSTRAINT `staff_shift_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `staff_shift_swap`
--
ALTER TABLE `staff_shift_swap`
  ADD CONSTRAINT `staff_shift_swap_ibfk_1` FOREIGN KEY (`request_shift_id`) REFERENCES `staff_shift` (`shift_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_swap_ibfk_2` FOREIGN KEY (`request_staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_swap_ibfk_3` FOREIGN KEY (`target_shift_id`) REFERENCES `staff_shift` (`shift_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_swap_ibfk_4` FOREIGN KEY (`target_staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_shift_swap_ibfk_5` FOREIGN KEY (`approval_staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stay`
--
ALTER TABLE `stay`
  ADD CONSTRAINT `stay_ibfk_3` FOREIGN KEY (`guest_id`) REFERENCES `guest` (`guest_id`),
  ADD CONSTRAINT `stay_ibfk_4` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`),
  ADD CONSTRAINT `stay_ibfk_5` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
