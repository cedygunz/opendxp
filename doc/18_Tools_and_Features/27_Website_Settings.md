# Website Settings

The `Website Settings` give you the possibility to configure website-specific settings, which you can 
access in every controller and view.

Examples:

* ReCAPTCHA public & private key
* Locale settings
* Google Maps API key
* Defaults
* ....

### Access the Settings

In controllers and views, you can use view helpers or argument resolves to access the config.
The returned configuration is an array containing your settings.


### Example Configuration

Usage in a template:

```twig
{# access the whole configuration #}
{{ opendxp_website_config() }}

{# or only a single value #}
{{ opendxp_website_config('googleMapsKey') }}

{# you can pass a default value in case the value is not configured #}
{{ opendxp_website_config('googleMapsKey', 'NOT SET') }}
```

Usage in a controller:

```php
<?php

use Symfony\Component\HttpFoundation\Response;

class TestController
{
    public function testAction(array $websiteConfig): Response
    {
        $recaptchaKeyPublic = $websiteConfig['recaptchaPublic'];
        
        // ...
    }    
}
```

### Manipulate the values in a Controller

If you want to change the value of a website setting from your PHP script, for example from a controller, you can use this code.

```php
<?php

use Symfony\Component\HttpFoundation\Response;

class TestController
{
    public function testAction(): Response
    {
        // Get the "some-number" setting for "de".
        // If the property does not exist, you will get the setting with no language provided
        $someSetting = \OpenDxp\Model\WebsiteSetting::getByName('some-number', null, 'de');
        $currentNumber = $someSetting->getData();
        
        // Now do something with the data or set new data (count up in this case)
        $newNumber = $currentNumber + 1;
        $somesetting->setData($newNumber);
        $somesetting->save();
        
        // ...
    }
}
```

### Events

You can listen to events when a website setting is loaded or changed.

**Load events** fire a `WebsiteSettingLoadEvent`, which carries a `type` constant indicating the access path:

| Constant                          | Fired by                              | Event type                             |
|-----------------------------------|---------------------------------------|----------------------------------------|
| `WebsiteSettingEvents::POST_LOAD` | `WebsiteSetting::getById()`           | `WebsiteSettingLoadEvent::TYPE_SINGLE` |
| `WebsiteSettingEvents::LIST_LOAD` | `Config::getWebsiteConfig()`          | `WebsiteSettingLoadEvent::TYPE_LIST`   |
| `WebsiteSettingEvents::DATA_LOAD` | `Config::getWebsiteConfigValue($key)` | `WebsiteSettingLoadEvent::TYPE_DATA`   |

**Mutation events** fire a `WebsiteSettingEvent`:

| Constant                            | Fired by   |
|-------------------------------------|------------|
| `WebsiteSettingEvents::PRE_ADD`     | `save()`   |
| `WebsiteSettingEvents::POST_ADD`    | `save()`   |
| `WebsiteSettingEvents::PRE_UPDATE`  | `save()`   |
| `WebsiteSettingEvents::POST_UPDATE` | `save()`   |
| `WebsiteSettingEvents::PRE_DELETE`  | `delete()` |
| `WebsiteSettingEvents::POST_DELETE` | `delete()` |


### HTTP Cache

When HTTP caching is enabled, website settings participate in tag-based cache invalidation automatically.

The tag added to a response depends on which access path triggered the load:

| Access                          | Tags added                                     |
|---------------------------------|------------------------------------------------|
| `WebsiteSetting::getById($id)`  | `website_setting_{id}`, `website_setting_list` |
| `opendxp_website_config('key')` | `website_setting_{id}`                         |
| `opendxp_website_config()`      | `website_setting_list`                         |

When a setting is saved or deleted, both `website_setting_{id}` and `website_setting_list` are invalidated.

> [!TIP]  
> Using `opendxp_website_config('key')` instead of `opendxp_website_config()` gives more granular cache invalidation: 
> only pages that loaded the changed setting are purged, not every page that loaded any setting.
