-- Create Database
CREATE DATABASE IF NOT EXISTS vishnu_food_bites;
USE vishnu_food_bites;

-- 1. Users table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') NOT NULL,
    session_token VARCHAR(32) NULL
);

-- 2. Shops table
CREATE TABLE Shops (
    shop_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_name VARCHAR(100) NOT NULL,
    shop_image VARCHAR(255)  -- to store image file path or URL
);

-- 3. Food Items table
CREATE TABLE Food_Items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT,
    item_name VARCHAR(100) NOT NULL,
    item_image VARCHAR(255),
    price DECIMAL(8,2),
    stock_status ENUM('in', 'out') DEFAULT 'in',
    quantity INT,
    FOREIGN KEY (shop_id) REFERENCES Shops(shop_id)
);

-- 4. Cart table
CREATE TABLE Cart (
    user_id INT NOT NULL COMMENT 'References the user owning the cart',
    item_id INT NOT NULL COMMENT 'References the food item in the cart',
    shop_id INT NOT NULL COMMENT 'References the shop of the item',
    item_name VARCHAR(100) NOT NULL COMMENT 'Name of the item for display',
    item_price DECIMAL(10,2) NOT NULL COMMENT 'Price of the item at time of adding',
    quantity INT NOT NULL COMMENT 'Number of units in the cart',
    PRIMARY KEY (user_id, item_id, shop_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Food_Items(item_id) ON DELETE CASCADE,
    FOREIGN KEY (shop_id) REFERENCES Shops(shop_id) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Stores cart items for Vishnu Food Bites';

-- 5. Orders table
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

-- 6. Order_Items table (for storing items inside each order)
CREATE TABLE Order_Items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT,
    quantity INT,
    FOREIGN KEY (item_id) REFERENCES Food_Items(item_id)
);


--7 . Accepted_Items
CREATE TABLE Accepted_Items (
    accepted_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    user_id INT,
    item_id INT,
    quantity INT,
    accepted_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (item_id) REFERENCES Food_Items(item_id)
);

--insertion into shops

INSERT INTO shops (shop_name, shop_image)
VALUES ('TempleSquare', 'images/templesquare.jpg');
INSERT INTO Shops (shop_name, shop_image)
VALUES 
('CentralSquare', 'images/centralsquare.jpg'),
('JuicyDrinks', 'images/juicydrinks.jpg'),
('Yummie', 'images/yummie.jpg');


--insertion into food items

INSERT INTO food_items (shop_id, item_name, item_image, price, stock_status)
VALUES
(1,'Milk Shakes', 'images/milkshakes.jpg', 100, 'In Stock'),
(1,'Cool Drinks', 'images/cooldrinks.jpg', 20, 'Out of Stock'),
(1,'Cakes','images/cakes.webp', 70, 'In Stock'),
(1,'Ice Creams', 'images/icecreams.webp',75, 'In Stock'),
(1,'Brownie', 'images/brownie.webp',60, 'Out of Stock'),
(1,'Rolls','images/rolls.jpg', 35, 'In Stock'),
(2,'Coffee','images/coffee.jpeg',20,'In Stock'),
(2,'Chocolates','images/chocolates.jpeg',25,'In Stock'),
(2,'Sandwich','images/sandwich.webp',60,'Out Of Stock'),
(2,'Chicken Puffs' ,'images/chickenpuffs.jpg',40,'In Stock'),
(2,'Veg Puffs', 'images/vegpuffs.jpg',35,'In Stock'),
(2,'Lays/Kurkure' ,'images/lays_kurkure.webp',10,'In Stock');


INSERT INTO food_items (shop_id, item_name, item_image, price, stock_status)
VALUES
(4,'French Fries', 'images/frenchfries.webp', 100, 'In Stock'),
(4,'Chicken Poppers', 'images/chickenpoppers.webp', 120, 'Out of Stock'),
(4,'Chicken Wings', 'images/chickenwings.webp', 150, 'In Stock'),
(4,'Veg Balls', 'images/vegballs.avif',85, 'In Stock'),
(4,'Chicken Burger', 'images/chickenburger.webp',160, 'Out of Stock'),
(4,'Veg Sticks', 'images/vegsticks.jpg', 95, 'In Stock'),
(3,'Grape Juice', 'images/grapejuice.jpg',40,'In Stock'),
(3,'Pomegranate Juice', 'images/pomegranatejuice.webp',45,'In Stock'),
(3,'Karbhuja Juice' ,'images/karbhujajuice.jpg',40,'Out Of Stock'),
(3,'Carrot Juice' ,'images/carrotjuice.jpeg',45,'In Stock'),
(3,'Watermelon Juice', 'images/watermelonjuice.jpg',30,'In Stock'),
(3,'Orange Juice' ,'images/orangejuice.webp',35,'In Stock');

-- to delete orders from the table if the time exceeds 24 hours (this is an event)
DELIMITER //

CREATE EVENT IF NOT EXISTS delete_old_orders
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM Orders
    WHERE order_date < NOW() - INTERVAL 24 HOUR;
END//

DELIMITER ;