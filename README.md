# Roller role management
Simple role management, with resource scope management, for Laravel.

This package is heavily inspired by [rolify](https://github.com/RolifyCommunity/rolify).

**This package is in alpha state, which means incomplete features and possible bugs. Don't use it for production.**

At the end of this file, there is a todo part that has some ideas for making this package better.

# Installation
 - `composer require vfsoraki/roller`
 - Add service provider `VFSoraki\Roller\RollerServiceProvider::class`
 - Publish config and migrations `php artisan vendor:publish --provider="VFSoraki\Roller\RollerServiceProvider"`
 - Run migrations `php artisan migrate`
 - Set the `User` class used by your app in `config/roller.php`. The default is Laravel's default

# Usage

## Definitions
A user is someone using your app. He/She should be authenticated in some other ways, as this package
provides authorization, not authentication.

A role is a capability a use has, like `admin` or `read` or `write`. Where a role or list of roles is required
by this methods of this package, you may provide a `Collection` or array of `VFSoraki\Roller\Role` models or simple
strings of role names, or even just a simple string of role name.

A resource is a class that you want to enforce a role on it. For example, suppose you have a post class. You
want to allow owner user of post to do anything to it. You simple give `owner` role on the post created by
user to that user. You also have an `admin` role that has access to every post. Also, you have `editor` users
who can see any post and may edit them, but you don't want them to delete posts. You simply have `owner`, `admin`
and `editor` roles, and set them globally or per-post when appropriate. Then when a user wants to edit a post,
you check if user has `admin` or `owner` or `editor` role on post.

## Hierarchical Roles
This system is designed to be hierarchical, which means if a user has `editor` role globally he/she also has `editor`
role on any resource.

Similarly, if a user has `editor` role on post model-types, he has `editor` role on every instance created
before or after creating this user or granting it `editor` role.


## Traits
To use this package, you should add `VFSoraki\Roller\RollerUser` trait to your user model. This adds methods to work
with roles on user models.

Also, to simplify using of resources, add `VFSoraki\Roller\RollerResource` trait to your resources.

## Usage
There are two methods available for adding roles: `giveRole` and `giveRoles` which are the same and you can use them
interchangeably. These are just to make sure you code remains semantically correct.

Similarly, there are two method for checking roles: `hasRole` and `hasRoles`.

### Add roles
To add a role to a user, use `giveRole` like this
```
// Grant globally
$user->giveRole('admin');
$user->giveRole(['read', 'write']);

// Grant on specific types
$user->giveRole('editor', Post::class);
$user->giveRole(['read', 'approve'], Post::class);

// Grant on specific instances
$user->giveRole('owner', $post);
$user->giveRole(['read', 'write'], $post);

```
`Post` may be any class, and there is no restrictions on that.

Note that `$post` does not have to use `VFSoraki\Roller\RollerResource` trait, but is **has** to be an Eloquent model.

Another thing is, these methods overwrite previous roles. Meaning after calling `giveRole`, the user only has roles
specified in the first parameter.

### Query roles
To check if user has roles, use `hasRole`
```
// Check global roles only
$hasRole = $user->hasRole('admin');
$hasRole = $user->hasRole(['read', 'write']);

// Check type-specific and global roles
$hasRole = $user->hasRole('editor', Post::class);
$hasRole = $user->hasRole(['read', 'approve'], Post::class);

// Check instance, type or global roles
$hasRole = $user->hasRole('owner', $post);
$hasRole = $user->hasRole(['read', 'write'], $post);

```
Note that `hasRole` return `true` if user has one or more of requested roles, `false` otherwise.

If you choose to use `VFSoraki\Roller\RollerResource` on you resources, you can also use `whoHasRoles` method provided
by trait. This method returns `Collection` of users who have specified role on that instance. For example
```
$users = $post->whoHasRoles('owner');
$users = $post->whoHasRoles(['read', 'write', 'approve']);
```
Note that this method returns users who have at least one of provided roles on instance.

# Todo
 - Write tests
 - Use Travis
 - Make working with roles more pleasurable, like creating a `getRoles` method and `addRole` method