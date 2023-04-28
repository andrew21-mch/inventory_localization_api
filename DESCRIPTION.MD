# Title: Localization of items in shops

This project aims to develop a system for easily searching and locating electronic components in retail shops. The system will consist of three main components: an API built with PHP/Laravel, a mobile and desktop application built with Flutter, and a hardware component built with Arduino C++.

The API will be responsible for managing the data related to the electronic components and their locations within the retail shop. It will provide endpoints for the mobile and desktop application to retrieve this information and perform searches. The API will also allow for the updating and management of the electronic component data.

The mobile and desktop application will be the primary interface for the end-users of the system. The application will allow users to search for electronic components by name or category and display their location within the retail shop. Users will also be able to scan barcodes of the components to quickly retrieve their location. The mobile application will be available for both Android and iOS devices, while the desktop application will be available for Windows and Mac OS.


The hardware component of the system will consist of an Arduino board with a WiFi module and LED lights attached to it. The Arduino board will be connected to the local WiFi network and will communicate with the server using an API developed using PHP/Laravel. The server will store the location data of each electronic component in the retail shop.

When a customer opens the app on their Flutter-powered device, they will be able to see a map of the retail shop, with the locations of the electronic components marked on it. The app will communicate with the server using the API, and the server will send back the location data of each component. The LED lights attached to the Arduino board will be programmed to turn on when the app requests the location of a particular component, making it easier for the customer to locate the desired component.

This system is expected to simplify the process of locating electronic components in retail shops, making it easier and faster for customers to find what they need. It is also expected to reduce the need for staff assistance, which can lead to increased efficiency and cost savings for the retail shop.

## Architecture

The system follows a client-server architecture, with the server being responsible for managing the data related to the electronic components and their locations within the retail shop. The server will provide endpoints for the mobile and desktop application to retrieve this information and perform searches. The server will also allow for the updating and management of the electronic component data.

The mobile and desktop application will be the primary interface for the end-users of the system. The application will allow users to search for electronic components by name or category and display their location within the retail shop. Users will also be able to scan barcodes of the components to quickly retrieve their location. The mobile application will be available for both Android and iOS devices, while the desktop application will be available for Windows and Mac OS.

The hardware component of the system will consist of an Arduino board with a WiFi module and LED lights attached to it. The Arduino board will be connected to the local WiFi network and will communicate with the server using an API developed using PHP/Laravel. The server will store the location data of each electronic component in the retail shop.

The shop owners will be able to manage their inventory using the mobile and desktop application. They will be able to add, remove, and update the electronic components in stock. They will also be able to view reports on inventory levels, sales, and other key metrics that they can use to make informed decisions about their business.

They will be able to search for electronic components by name, category, or manufacturer. when they select a component, they can click on the "locate" button, which sends an API request to the Arduino microcontroller. The microcontroller then uses the Wi-Fi module to communicate with the LED at the location of the selected component, and the LED lights up, making it easy for the user to locate the component.

### System Architecture Diagram

![System Architecture Diagram](https://github.com/andrew21-mch/inventory_localization_api/blob/69bb5e85b95c9e3aa2a6dd7375ee58613c47f870/public/Architecture2.png)

## Features

### Inventory Management
The system will allow the store owners to manage their inventory by adding, removing, and updating the electronic components in stock. This will help the owners keep track of their stock levels and know when to restock items.

### Component Search
The system will have a search feature that allows users to search for electronic components by name, category, or manufacturer. This will make it easy for users to find the component they need quickly.

### Location Assistance
When a user searches for a component and selects it, they can click on the "locate" button, which sends an API request to the Arduino microcontroller. The microcontroller then uses the Wi-Fi module to communicate with the LED at the location of the selected component, and the LED lights up, making it easy for the user to locate the component.

### Multiple Platform Support
The system will be developed using PHP/Laravel for the API, Flutter for the mobile app, and desktop app, and Arduino C++ for the hardware. This will allow users to access the system from different platforms and devices.

### User Management
The system will allow the store owners to manage their users by adding, removing, and updating user accounts. This will ensure that only authorized users can access the system.

### Reporting
The system will generate reports on inventory levels, sales, and other key metrics that the store owners can use to make informed decisions about their business.

### Notification System
The system will send notifications to the store owners when the stock levels of certain components reach a minimum threshold. This will help the owners know when to restock items and prevent stockouts.

### Secure and Scalable
The system will be designed to be secure and scalable, ensuring that it can handle a growing number of users and transactions without compromising on security. The system will be hosted on a secure server and will use encryption to protect user data.



The following technologies will be used to develop the system:

* API: PHP/Laravel
* Mobile App: Flutter
* Desktop Application: Flutter
* Hardware Component: Arduino C++


## Project Setup:

To set up the project, follow these steps:

* Install PHP/Laravel, Flutter, and Arduino C++ on your development environment
* Clone the repository
* Install the required dependencies for each component
* Set up the database by running the necessary migrations
* Start the API and hardware components
* Build and run the mobile and desktop applications


## Contributing:

We welcome contributions from anyone interested in improving the system. To contribute, please fork the repository and submit a pull request with your changes.

## License:

This project is licensed under the MIT License.
