-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 17, 2026 at 02:14 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `recipes_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `blockeduser`
--

CREATE TABLE `blockeduser` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `emailAddress` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `blockeduser`
--

INSERT INTO `blockeduser` (`id`, `firstName`, `lastName`, `emailAddress`) VALUES
(1, 'Sara', 'Alshehri', 'sara@gmail.com'),
(2, 'Ahmed', 'Alali', 'Ahmed@gmail.com'),
(3, 'Waleed', 'Altamimi', 'waleed@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`id`, `recipeID`, `userID`, `comment`, `date`) VALUES
(1, 1, 2, 'This looks amazing! My kids loved it.', '2026-02-19 23:14:35'),
(2, 2, 3, 'So tasty and easy to make.', '2026-02-25 23:14:35'),
(3, 3, 2, 'Simple and delicious.', '2026-01-12 23:14:35'),
(4, 4, 3, 'Perfect for lunch.', '2026-03-17 23:14:35'),
(5, 5, 2, 'Very healthy breakfast.', '2026-02-22 23:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `userID` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `favourites`
--

INSERT INTO `favourites` (`userID`, `recipeID`) VALUES
(2, 1),
(3, 2),
(2, 4),
(3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL,
  `ingredientName` varchar(100) NOT NULL,
  `ingredientQuantity` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `recipeID`, `ingredientName`, `ingredientQuantity`) VALUES
(1, 1, 'Butter', '150g'),
(2, 1, 'Light brown soft sugar', '150g'),
(3, 1, 'Honey', '4 tbsp'),
(4, 1, 'Porridge oats', '300g'),
(5, 1, 'Pear halves', '4'),
(6, 1, 'Dark chocolate', '100g'),
(7, 1, 'Salt', 'A pinch'),
(11, 3, 'Spaghetti', '250g'),
(12, 3, 'Pecorino cheese', '100g'),
(13, 3, 'Black pepper', '2 tsp'),
(14, 4, 'Chicken breast', '300g'),
(15, 4, 'Taco shells', '6'),
(16, 4, 'Lettuce', '1 cup'),
(17, 5, 'Oats', '1 cup'),
(18, 5, 'Milk', '1 cup'),
(19, 5, 'Banana', '1'),
(20, 5, 'Apple', '1'),
(33, 2, 'Rolled oats', '200g'),
(34, 2, 'Dried fruit', '100g'),
(35, 2, 'Honey', '3 tbsp');

-- --------------------------------------------------------

--
-- Table structure for table `instructions`
--

CREATE TABLE `instructions` (
  `id` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL,
  `step` text NOT NULL,
  `stepOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `instructions`
--

INSERT INTO `instructions` (`id`, `recipeID`, `step`, `stepOrder`) VALUES
(1, 1, 'Preheat the oven to 180°C.', 1),
(2, 1, 'Mix the oats and pears in a large bowl.', 2),
(3, 1, 'Bake for 20 minutes until golden brown.', 3),
(7, 3, 'Boil the pasta.', 1),
(8, 3, 'Mix cheese and pepper.', 2),
(9, 3, 'Combine with pasta and serve.', 3),
(10, 4, 'Cook chicken until done.', 1),
(11, 4, 'Assemble tacos with toppings.', 2),
(12, 4, 'Serve immediately.', 3),
(13, 5, 'Mix oats with milk.', 1),
(14, 5, 'Refrigerate overnight.', 2),
(15, 5, 'Add banana and apple, then serve.', 3),
(28, 2, 'Mix oats with dried fruit and honey.', 1),
(29, 2, 'Shape into cookies.', 2),
(30, 2, 'Bake until golden.', 3);

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `userID` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`userID`, `recipeID`) VALUES
(2, 1),
(3, 2),
(3, 3),
(2, 4),
(3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `recipe`
--

CREATE TABLE `recipe` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `photoFileName` varchar(255) DEFAULT NULL,
  `videoFilePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `recipe`
--

INSERT INTO `recipe` (`id`, `userID`, `categoryID`, `name`, `description`, `photoFileName`, `videoFilePath`) VALUES
(1, 3, 3, 'Pear & chocolate flapjacks', 'Get ready for a chewy, chocolatey adventure! These Pear & Chocolate Flapjacks are easy to make and delicious.', 'Pear-and-chocolate-flapjacks.jpg', 'https://youtu.be/ejfbKAN7j6A?si=zwPmdqyx-pDRXYH3'),
(2, 2, 3, 'Fruity flapjack cookies', 'Tasty fruity cookies that are perfect for kids and easy to prepare.', 'Fruity-flapjack-cookies.jpg', 'https://youtu.be/nfLtYKgS3lY?si=U94wGbwm7m1FMiUL'),
(3, 2, 2, 'Cacio e pepe', 'A simple pasta recipe with cheese and pepper.', 'Cacio-e-Pepe.jpg', 'https://youtu.be/UzhkMm7gV2w?si=t5BLq5mTE7wxdr2w'),
(4, 3, 2, 'Lighter chicken tacos', 'Delicious lighter chicken tacos with fresh toppings.', 'lighter-chicken-tacos.jpg', 'https://youtu.be/ALeF0GUCSSk?si=A7OnNoP58qlqABHQ'),
(5, 2, 1, 'Bircher muesli with apple & banana', 'Healthy breakfast recipe made with oats, apple, and banana.', 'bircher-museli-with-apple-banana.jpg', 'https://youtu.be/ngNs73KzFY8?si=auFN7cwVqEub1oaf');

-- --------------------------------------------------------

--
-- Table structure for table `recipecategory`
--

CREATE TABLE `recipecategory` (
  `id` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `recipecategory`
--

INSERT INTO `recipecategory` (`id`, `categoryName`) VALUES
(1, 'Breakfast'),
(2, 'Lunch'),
(3, 'Dessert');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `recipeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`id`, `userID`, `recipeID`) VALUES
(1, 2, 3),
(2, 3, 2),
(3, 2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `userType` enum('user','admin') NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `emailAddress` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photoFileName` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `userType`, `firstName`, `lastName`, `emailAddress`, `password`, `photoFileName`) VALUES
(1, 'admin', 'Maryam', 'Almaziad', 'maryam@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maryam.png'),
(2, 'user', 'Lama', 'Almubarak', 'lama@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'lama.jpg'),
(3, 'user', 'Tala', 'Alqahtani', 'tala@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tala.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blockeduser`
--
ALTER TABLE `blockeduser`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipeID` (`recipeID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`userID`,`recipeID`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `instructions`
--
ALTER TABLE `instructions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`userID`,`recipeID`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `recipe`
--
ALTER TABLE `recipe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexes for table `recipecategory`
--
ALTER TABLE `recipecategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`),
  ADD KEY `recipeID` (`recipeID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emailAddress` (`emailAddress`),
  ADD KEY `emailAddress_2` (`emailAddress`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blockeduser`
--
ALTER TABLE `blockeduser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `instructions`
--
ALTER TABLE `instructions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `recipe`
--
ALTER TABLE `recipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `recipecategory`
--
ALTER TABLE `recipecategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe`
--
ALTER TABLE `recipe`
  ADD CONSTRAINT `recipe_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `recipe_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `recipecategory` (`id`);

--
-- Constraints for table `report`
--
ALTER TABLE `report`
  ADD CONSTRAINT `report_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_ibfk_2` FOREIGN KEY (`recipeID`) REFERENCES `recipe` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
