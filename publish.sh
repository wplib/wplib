#!/usr/bin/env bash

function add_changed_files() {
    local changed_files="$(svn status "$1"/* | grep "^?" | awk '{print $2}')"
    if [ "" != "${changed_files}" ] ; then
        svn add "${changed_files}"
    fi
}


SVN_URL="https://plugins.svn.wordpress.org/wplib/"

# Capture space-trimmed version of first parameter
VERSION="$(echo $1 | xargs)"

if [ "" == "${VERSION}" ] ; then
    echo "Please specify the next version number of WPLib to publish to WordPress.org"
    exit;
fi

if [ "" == "$(git tag | grep "${VERSION}")" ] ; then
    echo "The version you requested (${VERSION}) has not been tagged in Git yet."
    exit;
fi

this_version="$(cat VERSION)"
if [ "${this_version}" != "${VERSION}" ] ; then
    echo -e "The version you requested is not the same as in the VERSION file: ${VERSION} <> ${this_version}"
    exit;
fi

echo "Publishing ${VERSION} of WPLib to wordpress.org/plugins/wplib"

rm -rf svn
mkdir -p svn
mkdir -p assets
svn co "${SVN_URL}" svn >/dev/null 2>&1
cd svn

rm -rf assets
mkdir -p assets
cp ../assets/images/* assets
add_changed_files assets

rm -rf trunk
mkdir -p trunk
cp ../wplib.php trunk
cp ../defines.php trunk
cp ../globals.php trunk
cp ../LICENSE trunk
cp ../README.* trunk
cp ../VERSION trunk
cp ../composer.json trunk
cp -R ../enums trunk
cp -R ../includes trunk
cp -R ../modules trunk
add_changed_files trunk

svn cp trunk "tags/${VERSION}"
add_changed_files "tags/${VERSION}"

svn status

svn ci --username MikeSchinkel -m "Publishing version ${VERSION}"

rm -rf svn

echo "WPLib ${VERSION} published."
