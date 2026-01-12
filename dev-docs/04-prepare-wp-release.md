## Prepare the `wp-release`
**For internal use only**

As we want to submit to WordPress.org plugin hub, we need to **include all libraries and assets to the package**. We use the GIT branch `wp-release` to keep this package.

We need to include all vendors to the repo then remove all `require` things in the composer.json file for skipping dependencies when this package being required.
- Switch to `wp-release` branch
- Delete all vendors
```
rm -rf vendor public-assets scripts src wp-app-config database resources
```

- Copy all needed files from master to this branch
```
git checkout master -- database public-assets resources scripts src wp-app-config .editorconfig composer.json composer.lock yivic-base-bootstrap.php yivic-base-init.php yivic-base.php package* yarn* *.js
```

- Install and add vendors
```
composer80 install --no-dev
```

- Prepare assets (should use node 20)
```
yarn install
yarn build
```

- Remove unused stuffs
```
rm -rf docker-compose*
bash scripts/delete-dotfiles-in-vendor.sh && bash scripts/delete-unnecessary-vendor-files.sh && bash scripts/delete-unnecessary-vendor-folders.sh
```

- Re-add assets and vendors
```
git add --force public-assets/dist vendor
```

- Remember to remove all packages in `composer.json` for not pulling them again when another project use this package on with `wp-release`
- Then add all files to the repo, commit and push