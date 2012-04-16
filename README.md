# Refugees United CLI #
## Produced in two days at the Rewired State Refugees United Hackday, 14/15th April 2012

### By [Samuel Littley](http://github.com/toastwaffle), [Robert Wright](http://www.github.com/PureEntropy), [Craig Snowden](http://github.com/CraigSnowden) and [Kevin Lewis](http://github.com/phazonoverload) ###

Released under the MIT License

A text-only method of interacting with the Refugees United system. Users can send several different text commands and receive a text-only response back. This system requires a seperate module to provide the interaction bridge between the communication channel (ie. SMS Chat, Voice Recognition) and this system. We've written our 'adapters' in Python and Ruby, however you can use anything that supports sending POST requests (cURL).

## Available Interaction Modules ##

* [SMS](http://github.com/toastwaffle/refutdcli-twilio)
* [XMPP](http://github.com/toastwaffle/refutdcli-xmpp)

## Installation & Usage ##

### To install: ###

* Edit config.php, adding all MySQL server and API details.
* Add the refutd.sql dump file to the database
* Upload other files to web server

### To use: ###

** DEMOs: **

* Use a browser to make a HTTP GET request to the respond.php file, with the following parameters:
    * 'message': Command to execute
    * 'guid': GUID of user to execute as

** PRODUCTION: **

* Visit the register.php file in a browser
* Fill in fields on form for user to connect as, and submit
* Record the 8 digit number given
* Send a HTTP POST to the register.php file, with the following parameter:
    * 'confirmcode': The 8 digit number as retrieved before
* Record the number in the response to the request. This is the user ID to use for subsequent requests.
* Send HTTP POSTs to the respond.php file, with the following parameters:
    * 'message': Command to execute
    * 'userid': User ID as obtained previously
* The response to the request will be the output of the command

In practice, the user should be informed if the Interaction Module detects that they are not registered, and linked to the registration page. The Interaction Module should accept the "REGISTER xxxxxxxx" command as given by register.php, and make the request for the user ID for subsequent requests. The Interaction Module should keep a database of a unique identifier for the users, and the user ID used for requests.

## Commands ##

Supported commands are:

* "search"/"find"/"look" <Search Term>
    * Searchs for <Search Term> and displays a list of numbered matches
    * Responding "more" will return extra results
    * Responding a number will return more details about the chosen person
* "update"/"set" <Field> <Value>
    * Updates the users profile
    * Valid fields are:
        * phone
        * email
        * lastname
        * physicaltraits
        * favoriteplace
        * favoritefood
        * favoriteactivity
        * hometown
        * dob
        * occupation
        * parentsnationality
        * familysize
        * firstname
        * gender
        * lastsighting
        * nickname
        * otherinformation
        * tribe
* "messages"/"inbox"/"unread"
    * Retrieves numbered list of (unread) messages
    * Responding more will return extra messages
    * Responding a number will display the chosen message
* sent"
    * Displays sent messages
    * Responding more will return extra messages
    * Responding a number will display the chosen message
* "message"/"email"/"e-mail"/"chat"/"call"/"mail"/"send" <Name>
    * Sends message to given person
    * TODO, NOT IMPLEMENTED. Only sends to self.
* details"/"info"/"whoami"
    * Retrieves and displays users profile
* "help"/"man" (<command>)
    * Displays help messages
    * If <command> not given, lists possible commands
    * If <command> given, gives help for command
        * TODO. Only "update" has help.

The system ignores "a"/"get"/"my"/"please" before any command, and "a"/"for"/"I'm"/"message"/"my"/"to" after any command.

## Copyright ##

Copyright (c) 2012 Samuel Littley, Robert Wright, Craig Snowden, Kevin Lewis.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.