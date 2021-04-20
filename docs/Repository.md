# Repository  
This document explains the general setup of the repository to develop, release and maintain Orkestra.

## Monorepo
This repository is a Monorepo containing the source code for Orkestra, its different components,
as well as some official adapters.
The main tool used to maintain the monorepo is the following [simplify/monorepo-builder](https://github.com/symplify/monorepo-builder).

You can still use the different components independently by requiring them using composer,
they are simply maintained as a single unit in this repository.

For contributing to Orkestra, you must contribute on this repository and not the standalone repositories
for the different components. The same goes for issues on github.


### Directory structure

```
docs/ # Contains the documentation of Orkestra and this repository.
components/ # Contains the different components of Orkestra
```

### Development
All components have their own `composer.json` to specify their dependencies individually. They are
then merged in a single `composer.json` at the root of this repository for development purposes.

Therefore, the root `composer.json` should be modified with care since some sections of it will be overwritten by
the merge tool.

The following sections need to be modified in the appropriate component's configuration individually:
- `require` 
- `require-dev`
- `autoload`
- `autoload-dev`
- `repositories`
- `extra`
  
The rest can be changed without any problem.

To ensure the dependencies are in sync between all components, run the following command:
```shell
vendor/bin/monorepo-builder validate
```

To perform a merge run the following command:
```shell
vendor/bin/monorepo-builder merge
```
> **Note**: The merge command also performs a validate prior to merging, meaning you don't have to run validate
> every time you make changes to one of the component's `composer.json`.



To release a new version:
```shell
vendor/bin/monorepo-builder release v7.0 --dry-run
```
> The --dry-run option allows seeing only what the steps the command will perform.

There are also version specific commands:

```shell
# For patches
vendor/bin/monorepo-builder release patch 
# For minor
vendor/bin/monorepo-builder release minor 
# For major
vendor/bin/monorepo-builder release major 
```

#### Working with an IDE
PHPStorm is capable out of the box to understand monorepo in the sense, that it supports multiple `composer.json` files as well as multiple `src` and tests` directories under the same
root directory.

You simply need to install the composer dependencies of the root, and you should be good to go.

To run the tests you can simply execute the following command: 
```shell
php vendor/bin/phpunit components/
```

When you make changes to a component always make sure that it works as a standalone package,
by installing its dependencies locally in its directory and running the tests in the same directory.


### Branching Model
The main branching model is based on the different versions of the library (For more information, see the [Versioning](#Versioning) section of this document).
Every major version has a dedicated branch (E.g.: 0.x, 1.x, 2.x, 3.x) which contains the latest changes for this branch.
The releases are made through git tags based off commits of these branches.


## Versioning
Orkestra releases follow semantic Versioning a.k.a [SemVer](ghttps://semver.org/).
In a nutshell the version numbers of the library are standardized and have the following form:

`x.y.z` where `x`is a major version, `y` a minor version and `z` a patch version.

- **Patch Versions** usually contain bug fixes or very small improvements such as typos, documentation addons. They aim to always be non-breaking changes
meaning that an application being on version `1.2.0` can safely upgrade to `1.2.1` without the need for the applications using the library
to do any code changes.
- **Minor Version** Usually contain new features and bug fixes requiring bigger changes. Similarly to patch versions, they aim to be
  backward compatible, meaning that applications using the library on version `1.2.4` for instance can safely upgrade to `1.3.0` without any need to do code changes
  to support this new version. 
  > **Note**: in some occasions, in order to avoid having too many major versions for some important and required feature, some breaking changes might happen in a minor version.
  Although we will try to keep these instances at a minimum, it is possible for it to happen from time to time.
  > These versions, will always have the required information to upgrade in the version release note.
- **Major Version**: It usually contains breaking changes and a lot of new features. Applications, usually need to do some code changes
in order to be able to upgrade to this new version.
  > **Note**: Upgrade guides are made available for major versions to ease and standardize this process.
  

The components of Orkestra always have their version bumped together as a unit. Therefore,
there should never be any case where one component would be at a different version of another component. This allows
all components to have the guarantee to work with all the other components at the same version number.

## Contributing

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

So for instance we could have the following:
> 