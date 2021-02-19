# Gym Accesses
This project was developed with the objective to monitor entrances for a martial arts gym during the Covid-19 pandemic. It offers the following functions:

* Sign up and user's information update
* Athletes addition
* Reservations for training sessions
* Management of date and times of sessions
* Generation of lists of participating athletes

The system consists in a web application that enables users to communicate their presence without forcing masters to handle too many interactions with participants.

## Structure of the application
The site is composed of four sections that can be reached from the main menu.

### Training attendance
Allows users to add and remove athletes, and to reserve or cancel registrations. In order to maximize the number of distinct persons that participate in a day's trainings, to athletes registered for N sessions in one day, only one will be guaranteed.
If necessary, the lowest-probability registrations will be reallocated to other persons (recalculating the original probabilities); on the other hand, if enough places are available, athletes will be assigned all the sessions they requested.

### Guide
This section introduces users to the application, explaining its uses. Different sections are shown based on the state of the user (visitor, user, master).

### Master Access
It is the administrative section of the application and allows to:
* View and download participation lists
* View the application's logs
* Manage date and times of training and cancellations
* Manage user permissions

### Profile
Username and password management.

## System
### Requirements
The original system consists in the following elements:
|                      |                  |
| -------------------- | ---------------- |
| Hardware             | Raspberry Pi 3b+ |
| Operating System     | Raspbian Linux 9 |
| Web Server           | Apache 2.4.25    |
| DBMS                 | MariaDB 10.1.48  |


### Languages and libraries
| Software                               | Version  |
| -------------------------------------- | -------- |
| [PHP](https://www.php.net/)            | 7.2      |
| [Bootstrap](https://getbootstrap.com/) | 4.4.1    |
| [Jquery](https://jquery.com/)          | 3.5.1    |
| [FPDF](http://www.fpdf.org/)           | 1.82     |

More information about the deployment are written in the READMEs for the [database](database/README-en.md) and [source code](src/README-en.md).

## Authors
[@rb-sl](https://github.com/rb-sl)

## License
This application is released under the [Apache 2.0 license](LICENSE).

## Images
![Mobile Register](images/mobile.png)

![Register](images/register.png)