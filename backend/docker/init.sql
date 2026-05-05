CREATE DATABASE IF NOT EXISTS parraindex_test;
GRANT ALL PRIVILEGES ON `parraindex_test`.* TO 'parraindex'@'%';
GRANT CREATE, DROP ON *.* TO 'parraindex'@'%';
FLUSH PRIVILEGES;
