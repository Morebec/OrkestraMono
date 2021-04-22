# Coding Convention
This document describes the coding standards and conventions for developing Orkestra in order to make
its code consistent.

## Committing
Commits messages should have the following form:
```
[ComponentName] Your commit message
```

Commit messages should always be in the imperative mood:
```
[Messaging] Update the Getting Started Documentation
[DateTime] Fix the offset not being applied correctly to the OffsetClock
[Normalization] Add documentation for the XYZ class
```

If you make a commit that changes multiple components at once, you can combine the component prefixes:
```
[Messaging][Normalization] Improve the bridge between messaging and Normalization
```

If you need to make changes to a lot of components, instead of combining component prefixes, try to see if 
it would not be possible to split your commit into multiple commits.

In some cases, there can be modifications that touches all the components, such as adding licenses, or changing
the coding standard, in this case the [All] Component prefix is accepted.

> Note that the name of the components between brackets, should not include the `orkestra-` prefix. Simply use the 
> actual name of the component in a PascalCase. E.g: Normalization, PostgreSQLEventStore, Messaging, Retry etc.

## Coding Standard
The coding standard is enforced using the `firendsofsymfony/php-cs-fixer` composer package that
provides a binary to fix the files according to the standard defined in the `php_cs.dist` file located 
at the root of the repository.

Before every commit you should run the following command:
```shell
vendor/bin/php-cs-fixer fix components/
```
This will ensure all the files you have modified adhere to the coding standard.

## Changelog
Every meaningful change should be documented in the `CHANGELOG.md` file located at the root of the directory
of the changed component. A meaningful change corresponds to new features and deprecations in minor or major versions.

Here's the structure of this file:
- The file starts with a main title `CHANGELOG`.
- Every major version should have a section starting with an H2 title with the name of the major version with `x` as its minor version and no patch version. (e.g. `1.x`)
- Minor versions should have a section starting with an H3 title with the name of the major version it is related to, its minor version and no patch version. (e.g. `1.1`)
- No other lower leveled sections should be present in the file.
- The file is meant to be read from bottom to top, meaning the latest changes are always on top.
- Every change entry should be done under a minor version section and should start with the component between square brackets, then with a verb in the past tense
and finally the rest of the message.

Here's an example file:

```markdown
# CHANGELOG

## 1.x


### 1.1
- [Normalizer] Added Fluent Normalizer and Denormalizer to define normalizers and denormalizers.

### 1.0

- [Messaging] Added Fluent Normalizer and Denormalizer to define normalizers and denormalizers.
```

> **Note:** Since Orkestra is a monorepo all component versions are kept in sync and therefore this should be reflected in the `CHANGELOG.md`file of each component.
> For more information on this see the [Repository](ContributionGuide.md#Versioning) document.


## Deprecating code
Sometimes a feature cannot be implemented or changed without breaking backward compatibility, and therefore causing breaking changes. To minize this as much as possible and offer users of the project a
way to either use the old implementation or the new alternative, we rely on the concept of deprecation.
With deprecation, we can mark classes or methods as "deprecated" generating warnings and indicating that these implementation details will be removed in the next versions.

To declare a method, class or property as deprecated, we use the `@deprecated` annotation in PHPDoc blocks.

A deprecation notice should indicate the version at which it started being deprecated, and when possible, the way it has been replaced.

E.g.: 
```php
/**
 * @deprecated since version 1.3, use {@link Replacement} instead.
 */
```
Also, to help developers understand the deprecation and update their code, in the deprecated class, method or property, trigger a PHP `E_USER_DEPRECATED` error:

```php
@trigger_error(sprintf('The "%s" class is deprecated since version 1.3, use "%s" instead.', Deprecated::class, Replacement::class), E_USER_DEPRECATED);

```

Next, add the deprecation notice in the `CHANGELOG.md` file:

```md
1.3
-----

* Deprecated the `Deprecated` class, use `Replacement` instead.
```

Finally, bump the minor of the version.

In summary, to deprecate a piece of code:

1. Add a PHPDoc block for the deprecated class, method or property.
1. Trigger a PHP `E_USER_DEPRECATED` error at the location of the deprecated code.
1. Document the deprecation in the `CHANGELOG.md` file.
1. Bump minor of the version.

> **Note**: There might be cases where we want to bundle multiple deprecations together in a single minor version, instead
> of one per deprecation.


### Removing deprecated code
The removal of deprecated code should only be done at least on the next major version. 
Once a deprecation has been removed, document it in the `CHANGELOG.md` file:

```md
2.0
---
* Removed the `Deprecated` class, use `Replacement` instead.
```

In summary, to remove a deprecated piece of code:

1. Remove the PHPDoc block for the deprecated class, method or property.
1. Remove the PHP `E_USER_DEPRECATED` error trigger.
1. Document the removal of the deprecation in the `CHANGELOG.md` file.
