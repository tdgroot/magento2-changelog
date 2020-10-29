# Magento 2 Changelog

Get insight into what has changed between Magento releases.

[See changelog for 2.3 release line](https://github.com/tdgroot/magento2-changelog/tree/2.3).

[See changelog for 2.4 release line](https://github.com/tdgroot/magento2-changelog/tree/2.4).

## Adding new releases

Adding a new release is quite simple, run the following:

```
git checkout 2.x
./update.sh 2.x.x
git add Magento
git commit -m "Add Magento 2.x.x"
```

## Rationale

Although Magento Open Source is, well, open source, the release process is not as open source as one would think. The release process happens at Magento HQ and the only outcome we (the community) get is a release tag in the Magento repository with thousands of commits between the new and previous tag.
