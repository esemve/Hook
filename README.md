# Hook engine for Laravel

**What is this?**

The purpose of this project is that your packages could modify each other without overriding the source code.

**What is a Hook?**

It is similar to an event. A code bounded by a hook runs unless a hook listener catches it and orders that instead of the function in the hook, something else should run. They could be set in an established order, so you are able to make several modifications in the code.

**What is it good for?**

Example 1: You have a module which displays an editor. This remains the same editor in every case.
If you bound the display of the editor in a hook, then you can write a module which can redefine/override this hook, and for example changs the textarea to a ckeditor.

Example 2: You list the users. You can include every line's print in a hook. This way you can write a separate module which could extend this line with an e-mail address print.

Example 3: You save the users' data in a database. If you do it in a hook, you can write a module which could add extra fields to the user model like "first name" or "last name". To do that, you didn't need to modify the code that handles the users, the extension module doesn't need to know the functioning of the main module.


... and so many other things. If you are building a CMS-like system, it will make your life a lot easier.

# How do I install it?

```bash
composer require esemve/hook
```

then to the app.php :
```php
...
'providers' => [
    ...
    Esemve\Hook\HookServiceProvider::class,
    ...
 ],
 'aliases' =>[
    ...
    'Hook' => Esemve\Hook\Facades\Hook::class
    ...
 ]
```


# How does it work?

Example:

```php
$user = new User();
$user = Hook::get('fillUser',[$user],function($user){
    return $user;
});
```

In this case a fillUser hook is thrown, which receive the $user object as a parameter. If nothing catches it, the internal function, the return $user will run, so nothing happens. But it can be caught by a listener from a provider:

```php
Hook::listen('fillUser', function ($callback, $output, $user) {
    if (empty($output))
    {
      $output = $user;
    }
    $output->profilImage = ProfilImage::getForUser($user->id);
    return $output;
}, 10);

```
The $callback contains the hook's original internal function, so it can be called here.

Multiple listeners could be registered to a hook, so in the $output the listener receives the response of the previously registered listeners of the hook.

THen come the parameters delivered by the hook, in this case the user.

The hook listener above caught the call of the fillUser, extended the received object, and returned it to its original place. After the run of the hook the $user object contains a profilImage variable as well.

Number 10 in the example is the priority. They are executed in an order, so if a number 5 is registered to the fillUser as well, it will run before number 10.


# Initial output

You can pass initial output to the listeners too.

```php
$initialOutput='test string';

\Hook::get('testing',['other string'],function($otherString){
    return $otherString;
},$initialOutput)

// and later ...

Hook::listen('testing', function ($callback, $output, $otherString) {
    if ($output==='test string') {
        $output="{$output} yeeeaaaayyy!";
    }
    if ($otherString==='other_string') {
        // other string is good too
    }
    return $output; // 'test string yeeeaaaayyy!'
});
```
If there is no listeners, 'other string' will be returned.

# Usage in blade templates

```php
@hook('hookName')
```

In this case the hook listener can catch it like this:
```php
 Hook::listen('template.hookName', function ($callback, $output, $variables) {
   return view('test.button');
 });
```
In the $variables variable it receives all of the variables that are available for the blade template.

:exclamation: **To listen blade templates you need to listen `template.hookName` instead of just `hookName`!**

# Wrap HTML
```php
@hook('hookName', true)
    this content can be modified with dom parsers
    you can inject some html here
@endhook
```
Now the `$output` parameter contains html wrapped by hook component.
```php
Hook::listen('template.hookName', function ($callback, $output, $variables) {
  return "<div class=\"alert alert-success\">$output</div>";
});
```

# Wildcards

You can use wildcards to attach the same listener to multiple hooks.
With wildcards, you can get another parameter `$wildcards` containing an array :
```php
Hook::listen('template.*', function($callback, $output, $variables, $wildcards = []) {
  // For the hook "template.foobar", $wildcards will contain : ["foobar"]
}):
```

:exclamation: Warning : the wildcard hook will be executed **only if there is no exact match** for the given hook name.
<br>For example :
```php
Hook::listen('*', function () {
  return 'wildcard';
});
Hook::listen('foo', function () {
  return 'foo';
});

Hook::get('foobar'); // => Will return 'wildcard'
Hook::get('bar'); // => Will return 'wildcard'
Hook::get('foo'); // => Will return 'foo'
``` 

# Stop
```php
Hook::stop();
```
Put in a hook listener it stops the running of the other listeners that are registered to this hook.



# For testing

```php
Hook::mock('hookName','returnValue');
```
After that the hookName hook will return returnValue as a response.

# Artisan

```bash
php artisan hook::list
```

Lists all the active hook listeners.

---

License: MIT
