#Easy API Authentication Bundle

[![License](https://img.shields.io/github/license/madrakio/easy-api-authentication-bundle.svg)](https://github.com/MadrakIO/easy-api-authentication-bundle/blob/master/LICENSE)
[![Code Climate](https://codeclimate.com/github/MadrakIO/easy-api-authentication-bundle/badges/gpa.svg)](https://codeclimate.com/github/MadrakIO/easy-api-authentication-bundle)
[![Packagist](https://img.shields.io/packagist/v/MadrakIO/easy-api-authentication-bundle.svg)]()
[![Packagist](https://img.shields.io/packagist/dt/MadrakIO/easy-api-authentication-bundle.svg)]()

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require madrakio/easy-api-authentication-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new MadrakIO\Bundle\EasyApiAuthenticationBundle\MadrakIOEasyApiAuthenticationBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Create your Application entity
-------------------------

```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MadrakIO\Bundle\EasyApiAuthenticationBundle\Entity\AbstractApplication;

/**
 * Application
 *
 * @ORM\Table(name="application")
 */
class Application extends AbstractApplication
{
}
```


Step 4: Configure the Bundle
-------------------------

config.yml
```yaml
madrak_io_easy_api_authentication:
    application_class: AppBundle\Entity\Application
```

security.yml
```yaml
security:
    #...
    firewalls:
        api_secured:
            pattern:   /api/.*
            stateless: true
            madrak_io_easy_api_authentication: true
```

Step 5: Sending Requests
-------------------------

To send a request to a Controller behind the MadrakIO Easy API Authentication Bundle you must send the following fields:

`key` - A Public Key from the Application table

`timestamp` - A timestamp within 300 seconds of the current timestamp

`verification` - A verification string created by the requesting application that matches the check in [ApiProvider.php](https://github.com/MadrakIO/easy-api-authentication-bundle/blob/master/Security/Authentication/Provider/ApiProvider.php#L30-L57)
