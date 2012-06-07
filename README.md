Opauth-Instagram
================
[Opauth][1] strategy for Instagram authentication.

Implemented based on http://instagr.am/developer/authentication/

Opauth is a multi-provider authentication framework for PHP.

Getting started
----------------
1. Install Opauth-Instagram:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/muhdazrain/opauth-instagram.git Instagram
   ```

2. Create a Instagram application at http://instagr.am/developer/clients/register/
   - Make sure that redirect URI is set to actual OAuth 2.0 callback URL, usually `http://path_to_opauth/instagram/int_callback`

3. Configure Opauth-Instagram strategy.

4. Direct user to `http://path_to_opauth/instagram` to authenticate


Strategy configuration
----------------------

Required parameters:

```php
<?php
'Instagram' => array(
  'client_id' => 'YOUR CLIENT ID',
  'client_secret' => 'YOUR CLIENT SECRET'
)
```

Optional parameters:
`scope`, `response_type`  
For `scope`, separate each scopes with a space(' ') and not a plus sign ('+'). Eg. `likes comments`.


References
----------
- [Instagram Developer Documentation](http://instagr.am/developer/authentication/)

License
---------
Opauth-Instagram is MIT Licensed  
Copyright © 2012 Muhammad Azrain Kamaruddin ([@muhdazrain][2])

[1]: https://github.com/uzyn/opauth
[2]: http://twitter.com/muhdazrain