Yii 2 Base Models Change Log
============================

2.0.0 under development
-----------------------

- Enh: Added `SubsidiaryTrait::hasSubsidiary()` to check whether the entity has the subsidiary or not.
- Enh: Added `OperatorTrait` to record operator who operate the entity.
- Enh: Added `createdAtToday` and `updatedAtToday` methods to specify the creation time or last updated time as today.
- Chg: `PasswordTrait` to set default value null to `PasswordResetToken` attribute. This may break compatibility.
