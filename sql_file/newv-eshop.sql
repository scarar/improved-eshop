-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 28, 2025 at 03:15 PM
-- Server version: 10.11.6-MariaDB-0+deb12u1
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`) VALUES
(1, 'Jewellery'),
(2, 'Cosmetics'),
(3, 'Apparels'),
(4, 'Garments'),
(5, 'Handmade'),
(6, 'Kitchen Assesories'),
(7, 'Socks'),
(8, 'Interiors');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer` varchar(50) NOT NULL,
  `vendor` varchar(50) NOT NULL,
  `order_bitcoin_address` text NOT NULL,
  `order_unique_id` varchar(500) NOT NULL,
  `product_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `quantity` float NOT NULL,
  `order_total` float NOT NULL,
  `payment_method_id` int(11) NOT NULL,
  `order_address` text NOT NULL,
  `order_additional_info` text NOT NULL,
  `order_status` varchar(50) NOT NULL,
  `ordered_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer`, `vendor`, `order_bitcoin_address`, `order_unique_id`, `product_id`, `price`, `quantity`, `order_total`, `payment_method_id`, `order_address`, `order_additional_info`, `order_status`, `ordered_at`) VALUES
(45, '0', 'aliusmanabbasi', '2Mwc86AVfCJ2SzC4TCqeHW1wXgWkQMMigNw', '23nassuhroontoisabbanamsuila20180321084330', 23, 0.2, 1, 0.2, 1, 'qweqweq', 'weqeqe', 'pending', '2018-03-21 08:43:37'),
(46, '0', 'aliusmanabbasi', '2NFbEpmdwW5FxUEJuG3ymAoBcZK6HX1NCgc', '23nassuhroontoisabbanamsuila20180321101851', 23, 0.2, 1, 0, 1, 'ad', 'asdasd', 'pending', '2018-03-21 10:18:55'),
(47, '0', 'aliusmanabbasi', '2N8JA1sRedzLRDYzJN5r13oztyiGADnDv5s', '23nassuhroontoisabbanamsuila20180324074928', 23, 0.2, 5, 0, 1, '151515', '15151515', 'pending', '2018-03-24 07:49:38'),
(48, 'test2', 'aliusmanabbasi', '2NAFLvA5dFiYAapqzcBTt3PArTKMtV2eYzw', '232tsettoisabbanamsuila20180330124148', 23, 0.2, 1, 0.2, 1, 'test 123', 'test123', 'pending', '2018-03-30 12:42:02'),
(49, 'test2', 'aliusmanabbasi', '2NDE3csb4t17rhb8SJHpfF6Rf5xzq2an5Nb', '232tsettoisabbanamsuila20180330124222', 23, 0.2, 1, 0.2, 1, 'test123', 'test123', 'pending', '2018-03-30 12:42:33'),
(50, 'test2', 'aliusmanabbasi', '2N7EzuQaTQ6jkCgoXxaGedVeVsu5h7sJEgZ', '242tsettoisabbanamsuila20180330124249', 24, 23, 1, 23, 1, 'test123', '', 'pending', '2018-03-30 12:42:57'),
(51, 'test2', 'aliusmanabbasi', '2My3Ae3UimvcLKjDa7TFsftnfx7o8E5qQqh', '232tsettoisabbanamsuila20180330124325', 23, 0.2, 1, 0.2, 1, 'kllk', '', 'pending', '2018-03-30 12:43:30'),
(52, 'test2', 'aliusmanabbasi', '2N9mkJxooLkDR2Ts5VE71s2W2gCb7QL9Bqm', '232tsettoisabbanamsuila20180330124343', 23, 0.2, 1, 0.2, 1, 'test123', '', 'pending', '2018-03-30 12:43:50'),
(53, 'test2', 'test2', '2N1tZrwpNbFKxG6gzxDkMjiTzoqMYQ9TJPk', '342tsetto2tset20180330124539', 34, 0.002, 1, 0.002, 1, '123test ave.', '', 'pending', '2018-03-30 12:45:56'),
(54, 'test2', 'test2', '2MzxckCjWqWYvNe9vndnnkXBgC6Wx4Zvg28', '342tsetto2tset20180330124610', 34, 0.002, 1, 0.002, 1, 'test4556', '', 'pending', '2018-03-30 01:33:08'),
(55, 'test2', 'aliusmanabbasi', '2N94qcmDAusRuPwNX94bz8dksW3cWUjkLup', '232tsettoisabbanamsuila20180401055501', 23, 0.2, 1, 0.2, 1, 'test', '', 'pending', '2018-04-01 05:55:14'),
(56, 'test2', 'aliusmanabbasi', '2NBHcTCoYNd8kKbKcd8FJRq4W9FRTnPWNFS', '232tsettoisabbanamsuila20180403071704', 23, 0.2, 1, 0.2, 1, 'test', '', 'pending', '2018-04-03 07:17:14'),
(57, 'noorhussan', 'aliusmanabbasi', '2Mx4HgMMpnqscKBgRJGH1RtjcW8XpjfSGp5', '23nassuhroontoisabbanamsuila20180403120221', 23, 0.2, 4, 0.8, 1, 'add', 'in', 'pending', '2018-04-03 12:02:29'),
(58, 'test2', 'aliusmanabbasi', '2MtTADgaFjtF8QeUrYcLwutABn5ptPpf6WR', '232tsettoisabbanamsuila20180404113734', 23, 0.2, 1, 0.2, 1, 't', '', 'pending', '2018-04-04 11:37:43'),
(59, 'test2', 'aliusmanabbasi', '2N5hAPj9MFMDCngL45LXaWG8UUG1dSSSJEg', '232tsettoisabbanamsuila20180404113827', 23, 0.2, 1, 0.2, 1, '4', '', 'pending', '2018-04-04 11:38:35'),
(60, 'noorhussan', 'aliusmanabbasi', '2MwjktDDwxqFo67hekccuHwLiWJpAMkNKV6', '23nassuhroontoisabbanamsuila20180407041755', 23, 0.2, 5, 1, 1, 'qweqewqwe', '13123123', 'pending', '2018-04-07 04:18:00'),
(61, 'aliusmanabbasi', 'aliusmanabbasi', '2N5wntrFwfSnmPmTg7FDPpa9foBJ8HBZYf9', '23isabbanamsuilatoisabbanamsuila20180418090439', 23, 0.2, 1, 0.2, 1, 'qweqwe', 'qweqweqe', 'pending', '2018-04-18 09:04:44'),
(62, 'test2', 'test2', '2N8mZpQdgYcqohVQaMD6tcahMveTNgwKVZV', '342tsetto2tset20180501032924', 34, 0.002, 1, 0.002, 1, 'test', '', 'pending', '2018-05-01 03:29:38'),
(63, 'test2', 'aliusmanabbasi', '2NBY9B8SnXXSQTKzqJqWYAKf2pk1kQC2DaZ', '232tsettoisabbanamsuila20180501033736', 23, 0.2, 1, 0.2, 1, 'test', '', 'pending', '2018-05-01 03:37:43'),
(64, 'test3', 'aliusmanabbasi', '38eUAfKbnDNQcY4HV4xzy5AaVf6TP6Vq5N', '233tsettoisabbanamsuila20180501040207', 23, 0.2, 1, 0.2, 1, 'test', 'test', 'pending', '2018-05-01 04:02:23'),
(65, 'test2', 'aliusmanabbasi', '3LEu4Mi8PJbWvZN6cgcxF6bLLQ39hvrZoK', '272tsettoisabbanamsuila20180501041516', 27, 15, 1, 15, 1, 'test', 'test', 'pending', '2018-05-01 04:15:26'),
(66, 'shit', 'aliusmanabbasi', 'bc1q8kqgfpp4sf77z8xaqf8lcyts95jt2ynhw00gp0', '23tihstoisabbanamsuila20230325075238', 23, 0.2, 1, 0.2, 1, '', '', 'pending', '2023-03-25 07:52:41'),
(67, 'shit', 'aliusmanabbasi', 'bc1qzyn3r6mzt34kkrdnfphdk79x0wrzgkn3vvja59', '23tihstoisabbanamsuila20230325075243', 23, 0.2, 1, 0.2, 1, '', '', 'pending', '2023-03-25 07:52:48'),
(68, 'shit', 'aliusmanabbasi', 'bc1q9dr732e6j0x63s4pt9my4vpt9q3hd5dsl3nucr', '23tihstoisabbanamsuila20230325075250', 23, 0.2, 1, 0.2, 1, '1', '1', 'pending', '2023-03-25 07:53:00'),
(70, 'fuckyou', 'shit', 'bc1qupttfshpd45s2y42lc3q9fck8k595fdg3wu2hj', '43uoykcuftotihs20230403063409', 43, 1, 1, 1, 1, 'dfefdffd', '', 'pending', '2023-04-03 06:48:22'),
(71, 'fuckyou', 'shit', 'bc1qmmp7usx3ytzsrhyuppceaz70qh4rjk97nmcgqs', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:12:41'),
(72, 'fuckyou', 'shit', 'bc1q5csjtc69y55w3yas59qhdqajtu62tre8dham49', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:14:31'),
(73, 'fuckyou', 'shit', 'bc1qhh48gcnnelm4kwy07ax35jlwjee85xxqkqturv', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:15:50'),
(74, 'fuckyou', 'shit', 'bc1qk80ur4vz687wzzpn6vu9m6ukn3utdr04xdc299', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:19:38'),
(75, 'fuckyou', 'shit', 'bc1qpdgc4ww4tze9850rrdlm204xqvucvhgw6qrvz9', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:27:43'),
(76, 'fuckyou', 'shit', 'bc1q4w5st9hmkga0mmae69am508xrkw6vpn5mrwfv8', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:28:50'),
(77, 'fuckyou', 'shit', 'bc1q66p3fqdm0hgdnfpvf6c302ccsz3ymwqyrtlc8j', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:29:18'),
(78, 'fuckyou', 'shit', 'bc1qndmhwkfx5pzdx6zuwtg08xlru76ghqpfw9l0rl', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:29:29'),
(79, 'fuckyou', 'shit', 'bc1qz2hga3dud8kle9tc76jakm5x4kgkqytx33gkh7', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:34:28'),
(80, 'fuckyou', 'shit', 'bc1qrlwueraqnranyntfshpt6svveph6e6zmevxurz', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:36:54'),
(81, 'fuckyou', 'shit', 'bc1qgpmqzvaezwv4c56tr5wt5ur334k33x8dm3flnt', '40uoykcuftotihs20230403071040', 40, 10, 1, 10, 1, 'dfdsfdsffdsf', '', 'pending', '2023-04-03 07:40:59'),
(82, 'test80', 'test80', 'bc1qq39atv8qw2yvzumnhq7fzuedvy9zm4ndgcsplw', '4408tsetto08tset20240821081834', 44, 2, 1, 2, 1, 'test81', 'test81', 'pending', '2024-08-21 08:18:58'),
(83, 'test443', 'test443', '', '46344tsetto344tset20240821091214', 46, 2, 1, 2, 1, 'gfhhghfhgf', '', 'pending', '2024-08-21 09:12:24'),
(84, 'test443', 'test443', '', '46344tsetto344tset20240821091729', 46, 2, 1, 2, 1, 'hjghjgjhj', '', 'pending', '2024-08-21 09:17:38'),
(85, 'test443', 'aliusmanabbasi', '', '27344tsettoisabbanamsuila20240821095850', 27, 15, 1, 15, 1, '999999', '', 'pending', '2024-08-21 09:58:57'),
(86, 'test443', 'test443', '', '46344tsetto344tset20240821095920', 46, 2, 1, 2, 1, '89978798798', '', 'pending', '2024-08-21 09:59:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_reviews`
--

CREATE TABLE `order_reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `vendor_id` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `items_delivered` tinyint(1) NOT NULL,
  `review_title` varchar(250) NOT NULL,
  `review_description` varchar(500) NOT NULL,
  `review_rating` int(11) NOT NULL,
  `rated_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_reviews`
--

INSERT INTO `order_reviews` (`review_id`, `user_id`, `vendor_id`, `product_id`, `items_delivered`, `review_title`, `review_description`, `review_rating`, `rated_on`) VALUES
(1, '2', '3', 4, 1, 'Awesome Product', 'This is an awesome beginner minimalist mechanical keyboard. I\'m already getting the hang of the fn keys for the arrows and such, and its way worth it for the minimalist. Driver CD came with it, but it already works great out of the box.', 5, '2018-03-19 00:00:00'),
(2, 'noorhussan', 'aliusmanabbasi', 23, 1, 'Great Quality Product', 'This is an awesome beginner minimalist mechanical keyboard. I\'m already getting the hang of the fn keys for the arrows and such, and its way worth it for the minimalist. Driver CD came with it, but it already works great out of the box.', 1, '2018-03-26 10:49:45'),
(3, 'noorhussan', 'aliusmanabbasi', 23, 1, 'WOW!!! Amazing Product', 'This is an awesome beginner minimalist mechanical keyboard. I\'m already getting the hang of the fn keys for the arrows and such, and its way worth it for the minimalist. Driver CD came with it, but it already works great out of the box.', 5, '2018-03-26 01:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `short_description` varchar(200) DEFAULT NULL,
  `meta_tags` varchar(250) DEFAULT NULL,
  `vendor` varchar(150) NOT NULL,
  `requires_fe` tinyint(1) NOT NULL,
  `product_type` varchar(50) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `image`, `price`, `description`, `short_description`, `meta_tags`, `vendor`, `requires_fe`, `product_type`, `category_id`) VALUES
(23, 'Sephla 14k White Gold Plated Forever Lover Heart Pendant Necklace', '5a480361773cf4-49125897.jpg', 0.2, 'This item does not ship to . Please check other sellers who may ship internationally. Learn more\r\nSold by sephla and Fulfilled by us. Gift-wrap available.', 'High polish finish and set with flawless cubic zir', 'necklace, pendant', 'aliusmanabbasi', 1, 'physical', 1),
(24, 'The Design of Design: Essays from a Computer Scientist 1st Edition', '5a48b07dcbf589-88460846.jpg', 23, 'Ships from and sold by us. Gift-wrap available.\r\nThis item ships to PK. Learn more', 'Making Sense of Design\r\nEffective design is at the', 'books, text, for sale,', 'aliusmanabbasi', 1, 'physical', 2),
(25, 'Breville BES870XL Barista Express Espresso Machine', '5a48b3e2a6aaa2-30901539.jpg', 430, 'Available from these sellers.\r\nPackaging may reveal contents. Choose Conceal Package at checkout.\r\nColor: Stainless Steel', '15 Bar Italian Pump and 1600W Thermo coil heating ', 'coffee, kitchen', 'aliusmanabbasi', 1, 'physical', 3),
(26, 'handmade Genuine Leather case for iphone 7 plus /8 plus leather case for iphone 7 / 8 leather wallet for iphone X', '5a48b532594401-41125377.jpg', 39, 'This phone wallet case is made from high-quality genuine distressed cowhide ,Hand sew with thick waxed thread ,so the amazing hand stitch is the one of most feature for this phone wallet', '****************************************\r\nThis pho', 'leather, handmade', 'aliusmanabbasi', 1, 'physical', 4),
(27, 'NIKE Performance Cushion Crew Socks with Band (6 Pairs)', '5a48b5808e3b70-18569079.jpg', 15, 'Get $50 off instantly: Pay $0.00 upon approval for the Amazon Rewards Visa Card.', 'Get $50 off instantly: Pay $0.00 upon approval for', 'socks, nike', 'aliusmanabbasi', 1, 'physical', 5),
(28, 'Durafit Seat Covers 2004-2008 Ford F150 Xcab Front 40/20/40.Seat belts come from top of seat, NOT FOR DOUBLE CAB XD3 Waterproof Camo Endura', '5a48b5b70b0925-20186162.jpg', 119, 'Get $50 off instantly: Pay $69.00 upon approval for the abc Rewards Visa Card.\r\nOnly 12 left in stock - order soon.\r\nThis item ships to us\r\nShips from and sold by Durafit Seat Covers.', '2004-2008 Ford F150 XLT Super Cab Exact Fit Seat C', 'seats, interior', 'aliusmanabbasi', 1, 'physical', 6),
(29, 'eGift Cards', '5a4a283eca0385-03989676.png', 200, 'cards', 'New Year cards ', 'cards, new year', 'aliusmanabbasi', 1, 'physical', 5),
(31, 'eGift Cards NEW', '5a4a29d3bdcc05-58640463.png', 25, 'cards', 'new year cards', 'cards', 'aliusmanabbasi', 1, 'physical', 5),
(32, 'eGift Cards NEW 02', '5a4a2bb927eb60-41640687.png', 25, 'cards', 'new year cards', 'cards', 'aliusmanabbasi', 1, 'physical', 5),
(33, 'test', '', 10, 'test', 'test', '', 'test2', 0, 'physical', 1),
(34, 'test22', '', 0.002, 'test', 'test', '', 'test2', 0, 'physical', 1),
(37, 'shit', '', 100, 'shit', '', 'shit', 'testorder', 1, 'physical', 1),
(40, 'testy', '641f402c7a5e66-53062476.jpg', 10, 'test', 'test', '1', 'shit', 1, 'physical', 1),
(43, 't', '641f49662996b1-42075605.jpg', 1, '1', '2', '1', 'shit', 1, 'physical', 1),
(44, 'test81', '', 2, 'test81', 'test81', '', 'test80', 1, 'physical', 1),
(46, 'test443', '', 2, 'test443', 'test443', '', 'test443', 1, 'virtual', 8);

-- --------------------------------------------------------

--
-- Table structure for table `product_meta`
--

CREATE TABLE `product_meta` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `meta_key` varchar(50) NOT NULL,
  `meta_value` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_meta`
--

INSERT INTO `product_meta` (`id`, `product_id`, `meta_key`, `meta_value`) VALUES
(1, 1, 'quantity', '15'),
(2, 32, 'quantity', '25'),
(3, 33, 'quantity', '1'),
(4, 34, 'quantity', '10'),
(5, 37, 'quantity', '10'),
(6, 40, 'quantity', '1'),
(7, 43, 'quantity', '1'),
(8, 44, 'quantity', '100'),
(9, 46, 'quantity', '1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(5) NOT NULL,
  `username` varchar(150) DEFAULT NULL,
  `password_hash` varchar(250) NOT NULL,
  `pin` varchar(20) DEFAULT NULL,
  `public_key` varchar(8000) DEFAULT NULL,
  `referral_code` varchar(150) DEFAULT NULL,
  `2fa_enabled` tinyint(1) DEFAULT NULL,
  `profile_image` varchar(250) DEFAULT NULL,
  `referral` varchar(150) DEFAULT NULL,
  `btc_payment_address` varchar(500) DEFAULT NULL,
  `btc_balance` decimal(16,8) DEFAULT 0.00000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `pin`, `public_key`, `referral_code`, `2fa_enabled`, `profile_image`, `referral`, `btc_payment_address`, `btc_balance`) VALUES
(27, 'romeo', '$2y$10$gq4VIUJkM/BSChYAZw6CbuHLmueH4NRks0XVDsK7U1GlzORrvoMt.', '1234', '-----BEGIN PGP PUBLIC KEY BLOCK-----\r\n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\r\n/8TDmfQoQ+zIMTmcQLklbME=\r\n=WffV\r\n-----END PGP PUBLIC KEY BLOCK-----\r\n', 'aliusman', 1, NULL, 'romeo5', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(28, 'test1', '$2y$10$gq4VIUJkM/BSChYAZw6CbuHLmueH4NRks0XVDsK7U1GlzORrvoMt.', '1234', '-----BEGIN PGP PUBLIC KEY BLOCK-----\r\n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\r\n/8TDmfQoQ+zIMTmcQLklbME=\r\n=WffV\r\n-----END PGP PUBLIC KEY BLOCK-----\r\n', '', 1, '', 'test111', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(29, 'aliusman', '$2y$10$ZRSunhkMVJQJrhwNkLP0mupRzY3lBqtxDUZwJ7ogxoqT3OXvn0TeK', '8800', NULL, 'romeo', NULL, '', 'aliusman11', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(32, 'rsp.stealth', '$2y$10$fQhf7CKEhZO5KYZf4u4cpeEtGw7S.g0Wpur1ZvwvEECSkTFQ19mwC', '8800', NULL, '', NULL, NULL, 'rsp.stealth11', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(33, 'aliusman2018', '$2y$10$RqAX.JXfDfZV98ImZ9B2Q.4OLKigJpJLRxhq.3PghKcwLe6cPilOS', '8800', NULL, '', NULL, NULL, 'aliusman201814', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(34, 'aliusmanabbasi', '$2y$10$D6uYVxFmY/9by6qeLtmef.SKQCaTwOceJlm4/Hcl0d3T6Bmljt1cG', '8800', NULL, '', NULL, '5a3f50bf055df2.33907106.png', 'aliusmanabbasi13', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(36, 'romeostealth', '$2y$10$qe7bSVoaNN/fCTxFYGJnrOBTraYzfvqibAIYhr4U4LVsf6yAq0z3q', '8800', NULL, '', NULL, NULL, 'romeostealth15', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(37, 'noorhussan', '$2y$10$HGT06DIWW2QwZh6Tn3uXhO.XGNJaHgIukBcpsFc6XIdl2FCzCZc3a', '8800', NULL, '', NULL, NULL, 'noorhussan8', 'A222SUT7PckXnL39ok1ra5wzuUCrEHu1S00 ', 0.00000000),
(38, 'peero', '$2y$10$YAi//vfEMXiTEUCN31albudGTo6n4UAMTDNWncu1r2gM59QCyqjxC', '9900', NULL, '', NULL, NULL, 'peero15', '2NEkawVj3S8XzxFytYDPHvK1yBvxYvkVuBR', 0.00000000),
(39, 'test3', '$2y$10$wii0/W1vky9BaQmP2RKTEeIKolgF3HztDpWv6UqA3nKskHl1Y9AqC', '0000', NULL, '', NULL, NULL, 'test39', '3LTrZNTdQBRGW9mVnWvcBzgsnnVZZ1rJpZ', 0.00000000),
(40, 'test2', '$2y$10$X88OYyoBnqfwVmeBQPzjoua3Cd4J1BitF64kMQ9co/d5Ks.2/RUiq', '1234', NULL, '', NULL, NULL, 'test28', '3LW7uhnuSm1rG4K8Lkzax9RXJnFkg3nJWp', 0.00000000),
(41, 'fuckyou', '$7$C6..../....4B7YXfDHcdbzeTPA19Rl5Nzd7RLw4iI3Q9YFIqGF8u4$cJKgyznO15r28MpOVkYKEQq1W8WrYvlKHgvLHjo/pC/', '1234', NULL, '', NULL, NULL, 'still13', 'bc1q3xtz6rhyfwkck6jkxl0g5cmc24gyaqvdck6nyp', 0.00006979),
(42, 'still1', '$7$C6..../....lOdkvV6/YczTMfnlq1H7pPmAztLQLnM4ghYEoN1RAd6$46yyzOp46Ky2onQAlycdB2CZyVlrDDcohrsauYz5sv3', '1234', NULL, '', NULL, NULL, 'still112', 'bc1qdwcvylk4rjw656768684q37et6rak6700r3jx4', 0.00084539),
(43, 'good', '$7$C6..../....MJk.PzNVFVsbh9.2Dm8orrjGxDNJ1giN5cW7VdTVfpB$t4Oti1LHv5Xd3zF5dEGOpCLtg6AKuiMTtyTlP61apS1', '1234', NULL, '', NULL, NULL, 'good15', 'bc1qzf2cvmzvjt30ygdtjquugttujwt33cqkgxedhx', 0.00000000),
(52, 'testme', '$7$C6..../....Tgr18fHaUizedzBqKb5tYMa9j.I/dYMWLl.C4f5Gu00$atE2eBuCO68356KPaIy9VU6kCO1lUALrfd2lnxOnw56', '1234', NULL, '', NULL, NULL, 'testme8', 'tb1qg2as08vmv907r2r004py0j4ujwjrlrx7zn8trv', 0.00000000),
(53, 'testme1', '$7$C6..../....or6RzJdPbqdQXg7uwRb/OdKikRr1hvekRqFm9xawGk.$ng2iiQq.UqjHHvmYhu/VZnfk9q2vuoCaG8403GU9qk3', '1234', NULL, '', NULL, NULL, 'testme110', 'tb1qekv0tf24dyd6vx3vyw3m94gwh4pr6qs6c32y6a', 0.00000000),
(54, 'test1234', '$7$C6..../....AXrDiSExBmcb4jmEsvFQRuT.O3ZNjniOJlq.eVn69.0$wYxnz8MegiD/cEa5Z31uyOPeMSYXDFnSD3tA5QzLB05', '1234', NULL, '', NULL, NULL, 'test12347', 'tb1qc9pgykd74y8q46k74jdy45g2ave44yl9xyxvye', 0.00009302),
(55, 'jj', '$2y$10$hMPEBRjVKxHf4oUMtjjO6.bUkKZl5EM4ZlqY7N15.v64R/86xhv66', '1234', NULL, '', NULL, NULL, 'jj15', '', 0.00000000),
(56, 'jj2', '$2y$10$SQyIGoVYPAyuzK5iHYJ5.uHbKIc9jQ18nQoikM9ikaVvi4OYskmpq', '1234', NULL, '', NULL, NULL, 'jj214', '', 0.00000000),
(57, 'jj3', '$7$C6..../....Sw/5UvcNh.wIzLMD7l8tdWFjb0r6JIHQPx16B/qxC//$L15fEtJqeiFx2uZPyosEPMEiUqfcl0KBNazyKawnHh4', '1234', NULL, '', NULL, NULL, 'jj314', '', 0.00000000),
(58, 'jj4', '$7$C6..../....Fl.99k2g3HkwNBUFKWcyMQwa0eQaijJCoC.YluWOFD8$chSgvl/AXBLTAyjYXdNwco4O5ak7kqEverm4rbuNNS.', '1234', NULL, '', NULL, NULL, 'jj412', 'bc1qqqxj4554y2aqewggh8tc6u2hcrswg2vu43z53k', 0.00000000),
(59, 'jj5', '$7$C6..../.....sKcC7Bq4Cj8vNKhadj/H9KQ7HneNzgu0D6scUAhqrC$on2z8nF8rSmLC1LXIBPIe0aY77inSMxGYcbu9/Bgsb2', '1234', NULL, '', NULL, NULL, 'jj58', 'bc1q68h0wrpapj60fr522pfcccjuwq7ccapwqay4cp', 0.00000000),
(60, 'jj6', '$7$C6..../....e8i4NDWs9/kc3iShu/zTC91mRV6Sr88CHVcXULzLI08$nrtfh8D0dUD80rogKq/92tY.raFL.PWHVLIGrXh3Y3B', '1234', NULL, '', NULL, NULL, 'jj68', 'bc1qyw52r9u9ld5x4hy42tm887jallxadsnjl8wlfs', 0.00000000),
(61, ' ', '$7$C6..../....2ULVq.6uDSLQkg.joF1PLvHvt14yuGvpliQipoPPeCB$.bN2Rwzj7u3H1UCNJW8TvMNXqEXCVM0Uz7v.ycywg9B', '1234', NULL, '', NULL, NULL, ' 5', 'bc1qsdmenjzsjek7g5yvrsk2845caarx5vhf70ct40', 0.00000000),
(62, 'test80', '$7$C6..../....DFrgSmDEUG7oku083vo9jKMH6RexvFpmefMJUvqB7lD$Irr2RftJgwRCnt3MZ0LEU9OJcbqgKn9zbxPpHBFcdX4', '1234', NULL, '', NULL, NULL, 'test8015', 'bc1q8jwlrgqje3uwy4jxjkpeag0l9v9k5fl8jjcalq', 0.00000000),
(63, 'test443', '$7$C6..../....0Zy7n3fDePClGOtL2IQT.QhO745vyZhaaKsjdmvurL/$OHZjCmzDrHor97UUQ1KQYj/BDQ/B4essoA3n8iMMMb.', '1234', NULL, '', NULL, NULL, 'test44314', 'bc1qrejn6weg7c6sdyasydfl4nracqv5kdugvq57k2', 0.00000000),
(64, 'test444', '$7$C6..../....jTFy7UASCsihRoDAQj8cyG8hcamIp040HLoskbs7eI6$CV9ZFTPC46O2WitYOi6BU2Q3K9.6Tdzd.hxg1tfugY4', '1234', NULL, '', NULL, NULL, 'test4448', 'bc1qqx7dsvcvgx7mkv3xcgn6gcrn85s6gjc8a8we9r', 0.00000000),
(65, 'test99', '$7$C6..../....Zk2/ANfV1OcY6eik.CdHgpwi0TGEsHMhih/ObdMIqXA$34SrvKvaYMegTkrMECjzJTw81C6lIaFlXplKWfvTvG2', '1234', NULL, '', NULL, NULL, 'test9915', 'bc1qx4jynlq8czxnjn3vqytpjp4mx23hz85p70cj8n', 0.00000000),
(66, 'bitcoinrpc_test', '$7$C6..../....anP2qn/Y8mVI3qykxni0kpjlS90R2i92cansZFzbtu8$3IIBlctNTHDiAxPS.ZStVGeLiJjkeTc6Y/p8lva9CZD', '1234', NULL, '', NULL, NULL, 'bitcoinrpc_test7', 'bc1qfm35rpulfmgh4ehkmg5zgqc39cru0yp3ql0uzy', 0.00000000),
(67, 'rpc_working', '$7$C6..../....G4iA3QCIsgqZPnGw.yVD/kC4vmNwVnx1n.pfLtECMu8$GP2TrxlwvFnxnME5eiyyzRw67BYfFeUF.UIfVDAS/e5', '1234', NULL, '', NULL, NULL, 'rpc_working8', 'bc1q3d0ctc5ycqrqdaencwc9a3zwljde2rm8ksgwmq', 0.00000000),
(68, 'get this bitch to work', '$7$C6..../....JTfaKXnYW6sXPFu8H0m4T7kEyOFx38qLwgedmWJrCu/$je7Eq8JlAhX2l5aYF4LTJtki4EZzcnArGsuD5YtWo70', '1234', NULL, '', NULL, NULL, 'get this bitch to work15', 'bc1qaakmfm5gsw2p30s5rt4xq4ydnql2w2de0an00t', 0.00000000),
(69, 'using tor', '$7$C6..../....0Oq1qgBhZWbig5CbYSTpHhBM.ezq1QLZnul8CHpNiX4$FtelxGqeVgsDVcdWVruP0J2LlHMdJXOJA5XhlHQV8h1', '2025', NULL, '', NULL, NULL, 'using tor8', 'bc1qzmtyzy3k4g67a0uwgmrxufvu4wp5yereexgh76', 0.00000000);

-- --------------------------------------------------------

--
-- Table structure for table `user_feedbacks`
--

CREATE TABLE `user_feedbacks` (
  `id` int(11) NOT NULL,
  `user_id` varchar(250) NOT NULL,
  `vendor_id` varchar(250) NOT NULL,
  `feedback_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_feedbacks`
--

INSERT INTO `user_feedbacks` (`id`, `user_id`, `vendor_id`, `feedback_value`) VALUES
(1, 'noorhussan', 'aliusmanabbasi', 2),
(2, 'aliusman', 'aliusmanabbasi', 2),
(3, 'romeo', 'aliusmanabbasi', 1),
(4, 'rsp.stealth', 'aliusmanabbasi', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_meta`
--

CREATE TABLE `user_meta` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meta_key` varchar(50) NOT NULL,
  `meta_value` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_meta`
--

INSERT INTO `user_meta` (`id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1, 34, 'user_description', 'manufactures exact fit seats covers for 2000 vehicles to every detail of their interiors. So you have an interior, which is easy to clean, maintain and look good.. So check if there is a listing for your vehicle and get a well protected interior for a very competative price, direct from the factory.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_reviews`
--
ALTER TABLE `order_reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `product_meta`
--
ALTER TABLE `product_meta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_feedbacks`
--
ALTER TABLE `user_feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_meta`
--
ALTER TABLE `user_meta`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `order_reviews`
--
ALTER TABLE `order_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `product_meta`
--
ALTER TABLE `product_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `user_feedbacks`
--
ALTER TABLE `user_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_meta`
--
ALTER TABLE `user_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
