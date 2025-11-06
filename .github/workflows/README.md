# GitHub Actions Workflows

This directory contains the GitHub Actions workflows for testing the NoSQL PHP library.

## Workflow Structure

### Core Workflows

- **`test-reusable.yml`** - Reusable workflow that handles the actual testing logic
- **`tests.yml`** - Main workflow that runs tests for all PHP versions on push/PR
- **`php83.yml`** - PHP 8.3 specific workflow
- **`php84.yml`** - PHP 8.4 specific workflow
- **`manual-tests.yml`** - Manual workflow for on-demand testing

### Usage Examples

#### Automatic Testing
Tests run automatically on:
- Push to `master`/`main` branches
- Pull requests to `master`/`main` branches  

#### Manual Testing
You can manually trigger tests by:
1. Go to the "Actions" tab in GitHub
2. Select "Manual Tests" workflow
3. Click "Run workflow"
4. Choose PHP version

### Features

- **Separate workflows** for each PHP version for better isolation
- **Reusable workflow** to avoid code duplication
- **Composer caching** for faster builds
- **Validation** of composer.json/composer.lock files
- **Combined status check** that requires all PHP versions to pass
