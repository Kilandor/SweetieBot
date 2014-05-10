# Setup
## Teamspeak Configuration
This should be your first step in configuration due to needing some data to setup your bot initially
1. Open Permission selecting the desired user or group
2. Input **i_client_serverquery_view_power** into the Filter make sure **Show Granted Only** is **unchecked* set this value to **75** or higher. This allows seeing ServerQuery Clients
3. Input **i_client_private_textmessage_power this needs** into the Filter make sure **Show Granted Only** is **unchecked* set this value to **75** or higher. This allows private messaging ServerQuery Clients
4. Open your Bookmarks and select Edit Bookmarks find the server your using. In the bottom left click the **More** button if it is not already expanded.
5. **Check** the **Show ServeryQuery Clients** box. Without this you cannot see them even with permissions
6. Next you will need your UniqueID. You may find this by going to Settings > Identity then choosing your identity its listed under **Unique ID** *(ex. 7oggBRGutT/h3Bt41YREVuAiMVc=)*. Leave this window open as you will need it in a moment.
7. Alternativaly to above if your using the default **ServerAdmin** group this group should always be ID **6**

### File Configuration
1. First you need to copy **inc/config-sample.php** or rename it to **config.php**.
2. Using your UniqueID or GroupID that you found on steps 6 or 7 above find the following code
```php
/*
$quick_config['full'] = array(
	6 => true
	);
*/
```
3. Uncomment it by removing the **/* ** and ** */** if your using the default Server Admin group your are complete. If your using a UniqueID replace the 6 with **'UNIQUE_ID_HERE'**
```php
$quick_config['full'] = array(
	'7oggBRGutT/h3Bt41YREVuAiMVc=' => true
	);
```
4. From there you can configure other options if need be. Most likely you will need group or channel id's.

# Starting the bot for your first time
Start the bot in your preferred manner. For linux you should create a screen to attach it to so it stays running and can be re-attached or stoped/restarted easily as needed.
If you do not see the bot check for errors on the window or logs. If you still do not see it please check the Teamspeak Configuration Steps

If everything went corrrectly you should see your bot now. You can private message it **help** to get started to see a list of commands.
You will likely want to start out by using **groupids** or **channelids** so you may further setup the configuration. After you configure you may simple issue the **reload** command to reload the configuration file.

And your bot should be ready to go.