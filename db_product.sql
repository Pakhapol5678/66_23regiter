CREATE TABLE products ( product_id INT AUTO_INCREMENT PRIMARY KEY, product_name VARCHAR(100) NOT NULL, description TEXT, price DECIMAL(10,2) NOT NULL, stock_quantity INT NOT NULL, category VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP );



INSERT INTO products (product_name, description, price, stock_quantity, category) VALUES ('ปากกาเจลลบได้', 'ปากกาเจลสีด า เขียนลื่น ลบได้', 35.00, 120, 'เครื่องเขียน'), ('สมุดโน้ต A5', 'สมุดขนาด A5 80 แผ่น ปกแข็ง', 45.00, 200, 'เครื่องเขียน'), ('กระบอกน้ าเก็บความเย็น', 'กระบอกน้ าสแตนเลส 500ml', 150.00, 50, 'ของใช้ส่วนตัว'), ('หูฟังบลูทูธ', 'หูฟังไร้สาย ระบบสัมผัส เชื่อมต่อ Bluetooth 5.0', 490.00, 35, 'อุปกรณ์อิเล็กทรอนิกส์'), ('หมวกกันแดด', 'หมวกปีกกว้าง ส าหรับกันแดดกลางแจ้ง', 120.00, 75, 'แฟชั่น'), ('กระเป๋าเป้สะพายหลัง', 'กระเป๋าใส่โน้ตบุ๊ก กันน้ า ขนาด 15 นิ้ว', 890.00, 20, 'แฟชั่น');

