# Helpdesk Sample API

**How to use this source code**

1. Install XAMPP, and start the Apache and mySQL 
2. Download this source code as zip
3. Extract the zip file in c:\xampp\htdocs
4. Rename the root folder as 'helpdesk'
5. Go to PHPMyAdmin, create new database name 'nre'
6. At nre database, run the sql command below: 

```
CREATE TABLE users(
user_id int AUTO_INCREMENT PRIMARY KEY,
username varchar(50),
password varchar(300), 
bahagian varchar(300), 
jawatan varchar(300), 
role varchar(300), 
tel varchar(300), 
name varchar(200), 
email varchar(300)); 


CREATE TABLE aduan(
aduan_id int PRIMARY KEY AUTO_INCREMENT, 
title varchar(300),
kategori varchar(300),
masalah text,
status varchar(300),
user_id_fk int,
created int

); 
```
7. Download and install Postman, make sure you sign up and log in.
8. Open Postman, click on import, then choose ```nre_api.json``` in \helpdesk folder
9. Run and test API!