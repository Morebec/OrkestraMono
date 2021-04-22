# Repository  
*This document explains the general the setup of the repository to develop, release and maintain Orkestra and its components.
If you intend on contributing to orchestra, this document is the place to start*

## Coding Convention
In order to have a common way of programming and committing to the repository, there is an adopted [Coding Convention](./CodingConvention.md).
Please read it carefully and apply it diligently, so your pull requests can be merged faster.

## Monorepo
This repository is a Monorepo containing the source code for Orkestra, its different components,
as well as some official adapters. The main tool used to maintain the monorepo is the following 
[simplify/monorepo-builder](https://github.com/symplify/monorepo-builder).

From a maintenance point of view, having a monorepo helps to ensure the components evolve together, since
they are meant to be combined. A monorepo greatly simplifies this process.

The component can still be used independently by requiring them using composer,
they are simply maintained as a single unit in this repository.

Whenever a new commit/release is made, a GitHub Action defined in the file [.github/workflows/split-repo.yaml](../.github/workflows/split-repo.yaml)
is triggered in order to split this monorepo into standalone packages that are linked to `packagist.org` for composer.

This split packages are intended to be read-only by both maintainers and users as they are basically just a projection
of the actual commits of the Monorepo.

For contributing to Orkestra, you must contribute to this mono-repository and not the standalone repositories. 
The same goes for issues on GitHub or questions.


### Directory structure
The monorepo has the following structure:
```
docs/ # Contains the documentation of this repository. The actual documentation for the different components is located in their own directories.
components/ # Contains the different components of Orkestra along with their documentation.
docker/ # Contains some docker-compose files in order to ease the **development** of orkestra components. 
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

#### Docker
The `docker/` directory contains development specific docker-compose configurations in order to quickly get a development
environment setup. When, developing Orkestra components one should use these configurations instead of the ones
that might be present in the components' directories, as these are meant for distributions for the users
using the components in their projects.

One of the reasons for this has to do with the paths that docker must use for the different volumes:
Essentially Orkestra being a monorepo, the `docker-compose` paths must be configured as such by
providing the root of the repository and not only the local directory of the server. However, in the case
of users of the components, they won't be using the packages from the monorepo, therefore their docker-compose configuration
need to be different.


```shell
# For orkestra framework
# Start
docker-compose --env-file=components/orkestra-framework/.env.local -f docker/orkestra-framework/docker-compose.yaml up -d

# Stop
docker-compose --env-file=components/orkestra-framework/.env.local -f docker/orkestra-framework/docker-compose.yaml stop

# Restart
docker-compose --env-file=components/orkestra-framework/.env.local -f docker/orkestra-framework/docker-compose.yaml restart

# PHP
docker-compose --env-file=components/orkestra-framework/.env.local -f docker/orkestra-framework/docker-compose.yaml exec php bash
```

#### Adding Composer dependencies:
Anytime you need to add a dependency to one of the component, add it to the component's `composer.json` file
then run the merge command:
```shell
vendor/bin/monorepo-builder merge
```

Then, run `composer update` to fetch the dependencies.

#### Validate interdependencies
To validate that the dependencies are in sync between all components, run the following command:
```shell
vendor/bin/monorepo-builder validate
```

> **Note**: The merge command also performs `validate` prior to merging, meaning you don't have to run validate
> every time you make changes to one of the component's `composer.json`, if you end up using the merge command.

#### Bump version number of all components
When a new version is in preparation for a release, it is important to bump the version number in all the `composer.json` files.
The following command allows to automate that task:

```shell
vendor/bin/monorepo-builder bump-interdependency "^2.0"
```

#### Release a new version
To release a new version execute the following command:
```shell
vendor/bin/monorepo-builder release v2.0 --dry-run
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
PHPStorm is capable out of the box to understand mono-repositories, in the sense that it supports multiple `composer.json` files 
as well as multiple `src` and `tests` directories under the same root directory.

You simply need to install the composer dependencies of the root, and you should be good to go.

To run the tests you can simply execute the following command: 
```shell
php vendor/bin/phpunit components/
```

This command will run the tests of all the components. You should always ensure that all the components unit tests
pass before committing to verify that your changes haven't introduced problems in other dependent components.

Also, when you make changes to a component always make sure that it works as a standalone package,
by installing its dependencies locally in its directory and running the tests in the same directory.


### Branching Model
The main branching model is based on the different versions of the library (For more information, see the [Versioning](#Versioning) section of this document).
Every major version has a dedicated branch (E.g.: 0.x, 1.x, 2.x, 3.x) which contains the latest changes for this branch.
The releases are made through git tags based on the commits of these branches.


## Versioning
Orkestra releases follow semantic Versioning a.k.a [SemVer](ghttps://semver.org/).
In a nutshell the version numbers of the library are standardized and have the following form:

`x.y.z` where `x`is a major version, `y` a minor version and `z` a patch version.

- **Patch Versions** usually contain bug fixes or very small improvements such as typos, documentation addons. They aim to always be non-breaking changes
meaning that an application being on version `1.2.0` can safely upgrade to `1.2.1` without the need for the applications using the library
to do any code changes.
- **Minor Versions** ordinarily contain new features and bug fixes requiring bigger changes than patch versions. Similarly to patch versions, they aim to be
  backward compatible, meaning that applications using the library on version `1.2.4` for instance can safely upgrade to `1.3.0` without any need to do code changes
  to support this new version. 
  > **Note**: in some occasions, in order to avoid having too many major versions for some important and required feature, some breaking changes might happen in a minor version.
  Although we try to keep these instances at a minimum, it is possible for it to happen from time to time.
  > These versions, will always have the required information to upgrade in the version release note.
- **Major Versions**: contain breaking changes and a lot of new features. Applications, generally need to do some code changes
in order to be able to upgrade to this new version.
  > **Note**: Upgrade guides are made available for major versions to ease and standardize this process.
  
The components of Orkestra always have their version bumped together as a unit. Therefore,
there should never be any case where one component would be at a different version from another component. This allows
all components to have the guarantee to work with all the other components at the same version number.
