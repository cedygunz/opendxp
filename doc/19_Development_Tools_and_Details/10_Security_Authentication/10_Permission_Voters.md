# Permission Voters

OpenDXP bridges its own permission checks (`User::isAllowed()` and `ElementInterface::isAllowed()`) into Symfony's authorization layer,
so you can use `#[IsGranted]` and `Security::isGranted()`.

## Two voters to rule them all

### UserPermissionVoter
Flat/global permission check, same as `$user->isAllowed($key)`. 
Keys come from `OpenDxp\Security\CorePermission` or any bundle's own permission enum (see below).

### ElementPermissionVoter
Element ACL check, same as `$element->isAllowed($type, $user)`. 
Keys come from `OpenDxp\Security\ElementPermission` (`View`, `Publish`, `Delete`, `Rename`, `Create`, `Settings`, `Versions`, `Properties`, `List`). 

Per-language edit/view permissions and layout restrictions are a separate, non-boolean mechanism and not part of this enum.

***

## Flat permission check

```php
#[IsGranted(CorePermission::Assets->value)]
class MyController extends AdminAbstractController
{
}
```

## Element ACL check

Declarative, with the element resolved from a controller argument:

```php
#[IsGranted(ElementPermission::View->value, subject: 'asset')]
public function myAction(Asset $asset): Response
{
}
```

Imperative:

```php
if ($security->isGranted(ElementPermission::View->value, $element)) {
    // ...
}

$security->denyAccessUnlessGranted(ElementPermission::Publish->value, $element);
```