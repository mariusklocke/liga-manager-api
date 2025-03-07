#!/usr/bin/env bash
set -eu

date=$(date +%Y-%m-%d)
echo "Please enter a version number (MAJOR.MINOR.PATCH)"
read -r version
if grep "## $version - $date" CHANGELOG.md > /dev/null; then
  echo "Changelog is valid: Contains supplied version and current date"
else
  echo "Changelog is invalid: Failed to find version and current date"
  exit 1
fi
changelog=$(git diff -U0 CHANGELOG.md | grep "^+\*" | cut -c 2-)
echo "These are changelog items found for $version"
echo "$changelog"
read -r -p "Are you sure you want to publish the release? [y/N] " response
case "$response" in
    [yY][eE][sS]|[yY])
        echo "Publishing release $version ..."
        git add CHANGELOG.md
        git commit -m "Publish $version"
        git push
        git tag -a "$version" -m "$changelog"
        git push origin "$version"
        gh release create "$version" --title "$version" --notes-from-tag
        echo "New version $version has been released"
        exit
        ;;
    *)
        echo "Release aborted. No changes have been made."
        exit
        ;;
esac
