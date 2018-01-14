#!/bin/bash
tar -czf build/build-$(date +%Y%m%d%H%M%S).tar.gz bin config data public src tests vendor composer.* *.xml
