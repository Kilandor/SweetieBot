Bot Readme

Setup Permissions
i_client_serverquery_view_power this needs to be > 75 to view servery query clients
i_client_private_textmessage_power this needs to be > 75 to private message server query clients

Open config and modify setup the server info, and make sure $cfg['cmd_auth_group'] is set to 0

Start the bot in your preferred manner. For linux you should create a screen to attach it to so it stays running and can be re-attached or stoped/restarted easily as needed.

If you do not see the bot in your server now first make sure you can see QueryClients. To do this you must be connecting from a bookmark. In the bottom left (click More) you will see Show Server Query Clients, check this and re-connect. If you still do not see your bot check it for errors, as the server config is likely wrong.

When the bot is running and you see it now. Open to send it a message, send it 'groupids' this will return a list of all the groups. Take the ID of the group you want (likely ServerAdmin) and modify the config and set this value for $cfg['cmd_auth_group'] this sets the group. So that group can then use all commands to the bot. Now restart the bot.

Now you can use the 'channelids' command to list your channels and setup afk mover and channel monitor. Simply grab the channel ID's set the appropriate configs then you may simply issue the bot a 'reload' command to reload the configs

And your bot should be ready to go.