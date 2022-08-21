Yii 2 Base Models Change Log
============================

2.0.0 under development
-----------------------

- Chg: `PasswordTrait` to set default value `null` to `PasswordResetToken` attribute.
- Chg: The minimum [MySQL](https://github.com/mysql/mysql-server) version is set to 8.0.
- Chg: `BlameableTrait` When setting host and updater, first check whether it is a string before checking the length to meet the PHP8.1 standard of `strlen()`.

1.1.0 June 07, 2017
-----------------------

- Enh: Added `SubsidiaryTrait::hasSubsidiary()` to check whether the entity has the subsidiary or not.
- Enh: Added `OperatorTrait` to record operator who operate the entity.
- Enh: Added `createdAtToday` and `updatedAtToday` methods to specify the creation time or last updated time as today.
