# Newsman Extension for OpenCart 4 - Configuration Guide

This guide walks you through every setting in the Newsman extension for OpenCart 4 so you can connect your store to your Newsman account and start collecting subscribers, sending newsletters, and tracking customer behavior.

---

## Where to Find the Extension Settings

After installing the extension, you will find the Newsman settings in two places:

- **Admin > Extensions > Modules > NewsMAN** (Edit button) - Main settings: API connection, subscriber sync, checkout newsletter, and developer settings
- **Admin > Extensions > Analytics > NewsMAN Remarketing** (Edit button) - Remarketing pixel and visitor tracking settings

---

## Getting Started - Connecting to Newsman

Before you can use any feature, you need to connect the extension to your Newsman account. There are two ways to do this:

### Option A: Quick Setup with OAuth (Recommended)

1. Go to **Admin > Extensions > Modules > NewsMAN** and click **Edit**.
2. Click the **Login with NewsMAN** button.
3. You will be taken to the Newsman website. Log in if needed and grant access.
4. You will be redirected back to a page in OpenCart where you choose your email list from a dropdown. Select the list you want to use and click **Save**.
5. That's it - your API Key, User ID, List, and Remarketing ID are all configured.

### Option B: Manual Setup

1. Log in to your Newsman account at newsman.app.
2. Go to your account settings and copy your **API Key** and **User ID**.
3. In OpenCart, go to **Admin > Extensions > Modules > NewsMAN** and click **Edit**.
4. Enter your **User ID** and **API Key** in the corresponding fields.
5. Select your **List** from the dropdown. The lists are fetched from Newsman using the credentials you entered.
6. Optionally select a **Segment**.
7. Click **Save**.

---

## Reconfigure with Newsman OAuth

If you need to reconnect the extension to a different Newsman account, or if your credentials have changed, go to the main Newsman settings page and click the **Reconfigure with Newsman Login** button. This will take you through the same OAuth flow described above - you will be redirected to the Newsman website to authorize access, then back to OpenCart to select your email list. Your API Key, User ID, List, and Remarketing ID will be updated with the new credentials.

---

## Main Settings Page

Go to **Admin > Extensions > Modules > NewsMAN > Edit** to configure the core extension behavior.

### Connection Settings

- **Module Status** - Enable or disable the Newsman module. When disabled, all Newsman features are inactive.

- **User ID** - Your Newsman User ID. Filled automatically if you used OAuth.

- **API Key** - Your Newsman API Key. Filled automatically if you used OAuth.

- **List** - Select the Newsman email list that will receive your subscribers. The dropdown shows all email lists from your Newsman account (SMS lists are excluded).

- **Segment** - Optionally select a segment within the chosen list. Segments let you organize subscribers into groups. If you don't use segments, leave this empty.

### Newsletter Settings

- **Double Opt-in** - When enabled (the default), new subscribers receive a confirmation email and must click a link to confirm their subscription. This is recommended for GDPR compliance. When disabled, subscribers are added to the list immediately.

- **Send User IP Address** - When enabled, the visitor's IP address is sent to Newsman when they subscribe or unsubscribe. This can help with analytics and compliance. When disabled, the **Server IP** address is sent instead.

- **Server IP** - A fallback IP address used when "Send User IP Address" is turned off. You can usually leave this empty.

### Export Authorization

- **Export Authorize Header Name / Key** - This is a legacy option for protecting your data exports with custom security credentials. If you connected via OAuth, you do not need to set these - the extension handles authentication automatically. You only need to fill these in if you set up the connection manually and want to add an extra layer of security to data exports.

### Multi-Store Options

These settings are only visible if you have multiple stores configured in OpenCart.

- **Export Subscribers by Store** - When enabled, only subscribers belonging to the current store are exported to Newsman. Disabled by default.

- **Export Customers by Store** - When enabled, only customers associated with the current store are exported. Customers in OpenCart can log in to all stores regardless of where they were created, so enable this if you want to filter them by store. Enabled by default.

### Developer Settings

These settings are intended for advanced users and developers. In most cases, you should leave them at their default values.

- **Log Level** - Controls how much detail the extension writes to its log file. The default is **Error**, which only logs problems. Set to **Debug** if you are troubleshooting an issue (but remember to set it back afterwards, as Debug mode creates large log files).

- **Log Clean Days** - Automatically deletes log files older than this number of days. The default is 60 days.

- **API Timeout** - How many seconds the extension waits for a response from Newsman before giving up. The default of 10 seconds works well for most setups.

- **Enable Test User IP / Test IP** - For development and testing only. Lets you simulate a specific visitor IP address. This option should not be enabled in a production environment.

---

## Remarketing Settings

Go to **Admin > Extensions > Analytics > NewsMAN Remarketing > Edit** to configure visitor tracking.

Remarketing lets Newsman track what pages and products your visitors view, so you can send them personalized emails (e.g., abandoned cart reminders, product recommendations).

- **Status** - Enable or disable the remarketing tracking pixel on your store. Enabled by default.

- **NewsMAN Remarketing ID** - This identifies your store in the Newsman tracking system. It is filled in automatically if you used OAuth. You can also find it in your Newsman account under remarketing settings.

- **Anonymize IP Address** - When turned on, visitor IP addresses are anonymized before being sent to Newsman. Recommended for GDPR compliance. Enabled by default.

- **Send Phone Number** - Include customer phone numbers in remarketing data. Only applies to logged-in customers who have provided a phone number. Enabled by default.

- **Theme Compatibility Mode** - Some third-party themes do not render the default OpenCart analytics output in their templates. If your remarketing scripts are not appearing on the storefront, enable this option. When enabled, the remarketing scripts are injected via an OpenCart event instead of relying on the theme to output them. After changing this setting, check your storefront page source to verify the remarketing scripts appear exactly once to avoid duplicate scripts.

### What Gets Tracked

The remarketing pixel automatically tracks visitor activity on your store:

- **Product pages** - Records which products visitors view
- **Category pages** - Records which categories visitors browse
- **Shopping cart** - Records cart contents and value
- **Order confirmation** - Records completed purchases with order value and items
- **All other pages** - General page view tracking

---

## Frequently Asked Questions

### How do I know if the connection is working?

After entering your credentials and saving, check that the **List** dropdown shows your Newsman lists. Every Newsman account has at least one list by default, so if the credentials are correct the lists will appear.

### What is Double Opt-in?

When Double Opt-in is enabled, new subscribers receive a confirmation email with a link they must click to confirm their subscription. This ensures the email address is valid and that the person actually wants to subscribe. Double Opt-in is recommended for GDPR compliance.

### The remarketing scripts are not showing on my storefront. What should I do?

If you use a third-party theme, it may not render the default OpenCart analytics output. Go to **Admin > Extensions > Analytics > NewsMAN Remarketing** and enable the **Theme Compatibility Mode** option. Then check your storefront page source to verify the scripts appear.

### Where are the extension logs?

The extension writes logs to `storage/logs/newsman_*.log` files. The logging level is controlled in Developer Settings. Log files older than the configured number of days (default: 60) are automatically cleaned up.

### Can I configure different lists for different stores?

Yes. All settings support OpenCart's multi-store system. Configure different lists, segments, or remarketing IDs for each store.

### What happens when a customer subscribes to the newsletter?

When a customer subscribes through account registration or their account settings page, the extension automatically sends the subscription to Newsman using the configured list and segment. If Double Opt-in is enabled, Newsman will send a confirmation email first.
