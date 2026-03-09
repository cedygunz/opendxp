# Working With Sites

## Introduction
With Sites, it is possible to create multi domain web applications within OpenDXP. 
 Starting point is always a certain node in the Documents tree. From this starting point on, the whole sub tree can appear
 as an own Site and is reachable with a certain domain.  

## Configuring Sites
You can create subsites in OpenDXP very easily directly in the context menu of the Document tree: 

![Configuring Sites](../../img/sites.png)

That's basically all.
 
 Note: Of course, your server setup (VHost, `ServerAlias`) must be configured properly so that all the requests for all the 
 domains are delegated to OpenDXP. 
 
 
Now also the routing functionalities [Custom Routes](./02_Custom_Routes.md) and [Redirects](./04_Redirects.md) 
can be configured site specific. 
Also, lots of other OpenDXP tools and functionalities like Glossary, Tag & Snippet Management, Marketing Settings 
(Google Analytics, Google Search Console, Google Tag Manager) and Website Settings are site specific. 


## Sites in your Code

#### Check if Current Request is Inside a Subsite

<div class="code-section">

```php
if(\OpenDxp\Model\Site::isSiteRequest()) { /* ... */ }
```

```twig
{% if opendxp_site_is_request() %}
    {# ... #}
{% endif %}
```

</div>

#### Working with the Navigation Helper
See [Navigation](../../03_Documents/03_Navigation.md) for more information. 


#### Getting the full path of a document inside a subsite-request

<div class="code-section">

```php
$document->getRealFullpath(); // returns the path including the site-root
$document->getFullPath(); // returns the path relative to the site-root
```

```twig
document.getRealFullpath()   {# returns the path including the site-root #}
document->getFullPath()  {# returns the path relative to the site-root #}
```
</div>

#### Getting the root-document of the current site

<div class="code-section">

```php
if (\OpenDxp\Model\Site::isSiteRequest()) {
    $site = \OpenDxp\Model\Site::getCurrentSite();
    $navStartNode = $site->getRootDocument();
} else {
    $navStartNode = \OpenDxp\Model\Document::getById(1);
}
```

```twig
    {% if opendxp_site_is_request() %}
        {% set site = opendxp_site_current() %}
        {% set navStartNode = site.getRootDocument() %}
    {% else %}
        {% set navStartNode = opendxp_document(1) %}
    {% endif %}
```

</div>

#### Some other Tools
The functionality should be pretty self-explanatory: 
```php
\OpenDxp\Tool\Frontend::getSiteForDocument($document);
\OpenDxp\Tool\Frontend::isDocumentInCurrentSite($document);
\OpenDxp\Tool\Frontend::isDocumentInSite($site, $document);
```

#### Document Preview Navigation with Sites
Please keep in mind that when previewing documents that have links to different Sites, the navigation may not be working properly due the Iframe Content Security Policies and Cross-origin resource sharing (CORS) policy, please set your own security rules accordingly to your own needs.


## General Domain in Multi-Site Setups

When no request is available (e.g. in CLI commands) OpenDXP needs a fallback "general" domain that is
independent of any specific site. See [Domain and Host Handling](./06_Domain_and_Host_Handling.md) for
configuration options, including how to drive this value from site custom settings in the backoffice.
