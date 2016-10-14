#Experimental Penguins Remake Source

##Overview:

A while ago I made a remake of Experimental Penguins based on a version shown in a video made by Rocketsnail. This was an unarchived version of Experimental Penguins, also known as penguins-113.swf. The webpage where the SWF belonged existed but not the actual SWF file.

##This source includes:

    penguins-113.fla, the client for the game.
    org/ascrypt/, an AS2 library for various encryptions.
    XPeng.php, the server for the game
    A.C.M.E Explosive!.ttf, a font necessary for the client.

Make sure you read README so you can properly setup the Security Features of the game.

**THE ORDER OF THE README INSTRUCTIONS IS IMPORTANT!!!!**

##Features:

    Cool admin commands like kick and ban.
    Passwords hashed in HMAC-SHA-256.
    Server responses are heavily encrypted.
    Hidden Admin only Watch Tower room which lets them watch all users invisibly.

##Setup:
What you need:  
	-Adobe Flash CS6 or lower.  
	-Any text editor (TextEdit, Sublime Text, Notepad++, Nano, Emacs or Vim).  
	-Apache & PHP.  
	
What you don't need:
	-A spoon.

What you need to do with what you need:

	Setting up the Server (DO BEFORE SETTING UP CLIENT):
		-Save XPeng.php in your server directory.
		-Go to XPeng.php via browser.
		-Click on "Generate Key"
		-Copy the stuff you get. Something like: 0OXO-597C-ML2K-NIE3-ER52-1JX1-BXUX-U1EV-PBKV
		-Open XPeng.php in your text editor.
		-Find and replace PUT_GLOBAL_KEY_HERE with the stuff you got earlier.
		-Save it.

	Setting up the Client:
		-Install A.C.M.E Explosive!.ttf font
		-Open up penguins-113.fla in Adobe Flash CS6 or lower.
		-Copy the stuff you got while setting up the server.
		-Find and replace PUT_GLOBAL_KEY_HERE with the stuff you got earlier.
		-Save it.
		-Press Control(Command on Mac)+Enter, this should make penguins-113.swf.
		-Move penguins-113.swf to the server directory.

	Playing the Game:
		-Before you login you will want to setup the Admin account
		-When the game starts click "CREDITS"
		-Then press ` or ~ on your keyboard.
		-A login form should pop up.
		-Enter Admin as your username.
		-Enter password as your password.
		-After your in type in the box /mYOUR_NEW_USERNAME:YOUR_NEW_PASSWORD:2
		-Then press enter.
		-Now press /dAdmin.
		-Now logout.
		-Now login again with your new username and password.

	Commands(only for admins):
		/m[username]:[password]:[account type]
			Saves a user, account type 1 is reserved name & account type 2 is administrator
		/d[username]
			Deletes a saved user
		/k[username]
			Kicks user off server
		/b[username]
			Bans a users IP address

	Usertypes:
		0-1: Reserved names, requires password
		2: Administrator
