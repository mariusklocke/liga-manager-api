#!/usr/bin/env bash
set -e

echo "Please enter a version number (MAJOR.MINOR.PATCH)"
read -r version
sed -i "/## NEXT/ a \\\n## $version - $(date +%Y-%m-%d)" CHANGELOG.md
echo "Changelog has been modified. These are the first 15 lines:"
head -n 15 CHANGELOG.md
read -r -p "Are you sure you want to publish the release? [y/N] " response
case "$response" in
    [yY][eE][sS]|[yY])
        echo "Publishing release $version ..."
        git add CHANGELOG.md
        git commit -m "Publish $version"
        git push
        git tag "$version"
        git push origin "$version"
        echo "New version $version has been released"
        exit
        ;;
    *)
        git checkout CHANGELOG.md
        echo "Release aborted. Changelog was reverted."
        exit
        ;;
esac
