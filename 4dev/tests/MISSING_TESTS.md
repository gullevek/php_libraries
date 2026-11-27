# Missing Tests

Overview of public methods in `www/lib/CoreLibs` that are not covered by a test in `4dev/tests`.

Generated: 2026-11-27

## How this was checked

- All public methods declared in each class/trait/enum were read via reflection (inherited and trait-imported methods are listed under their own file).
- A method counts as tested if a related test file (one that references the class) calls it via `->method`, `::method`, a quoted method name, or `new Class(` for constructors. Comments are ignored.
- The dynamic `Color::$target()` calls in CoreLibsConvertColorTest were resolved manually.
- `__destruct` is not counted as missing.
- Interfaces are not listed. They are covered through their implementations.
- This is a static check. It shows methods that are never *called* from a test, not line or branch coverage. For that, run `phpunit --coverage-html`.

## 1. Test files that are only placeholders

These test files exist but only call `markTestSkipped`/`markTestIncomplete`, so nothing in the class is tested.

### `Admin/Backend.php`

- Test file: `Admin/CoreLibsAdminBackendTest.php`
- Missing (12): `__construct`, `setACL`, `adbGetAcl`, `adbSetActionVars`, `adbGetActionSet`, `adbEditLog`, `adbSetMenuShowFlag`, `adbGetMenuShowFlag`, `adbTopMenu`, `adbMsg`, `adbLiveQueue`, `adbPrintDateTime`

### `Admin/EditBase.php`

- Test file: `Admin/CoreLibsAdminEditPageTest.php (file is named EditPage, class is EditBase)`
- Missing (2): `__construct`, `editBaseRun`

### `DB/Extended/ArrayIO.php`

- Test file: `DB/CoreLibsDBExtendedArrayIOTest.php`
- Missing (21): `__construct`, `setTableArray`, `getTableArray`, `getTableArrayEntry`, `setTableArrayEntry`, `unsetTableArrayEntry`, `setTableName`, `getTableName`, `setPkName`, `getPkName`, `setPkId`, `getPkId`, `dbArrayIOSetAcl`, `convertData`, `convertEntities`, `dbDumpArray`, `dbCheckPkSet`, `dbResetArray`, `dbDelete`, `dbRead`, `dbWrite`

### `Output/Form/Elements.php`

- Test file: `Output/CoreLibsOutputFormElementsTest.php`
- Note: the test is `markTestIncomplete`
- Missing (2): `printDateTime`, `magicLinks`

### `Output/Form/Generate.php`

- Test file: `Output/CoreLibsOutputFormGenerateTest.php`
- Missing (25): `__construct`, `getAclAdmin`, `checkBaseACL`, `formDumpTableArray`, `formGetColNameFromKey`, `formGetColNameArrayFromKey`, `formPrintMsg`, `formProcedureLoad`, `formProcedureNew`, `formProcedureSave`, `formProcedureDelete`, `formProcedureDeleteFromElementList`, `formCreateLoad`, `formCreateNew`, `formCreateSaveDelete`, `formCreateElement`, `formErrorCheck`, `formSetOrder`, `formUnsetTableArray`, `formLoadTableArray`, `formSaveTableArray`, `formDeleteTableArray`, `formCreateHiddenFields`, `formCreateElementReferenceTable`, `formCreateElementListTable`

### `Output/Form/Token.php`

- Test file: `Output/CoreLibsOutputFormTokenTest.php`
- Note: the test is `markTestIncomplete`
- Missing (2): `setFormToken`, `validateFormToken`

### `Output/ProgressBar.php`

- Test file: `Output/CoreLibsOutputProgressbarTest.php`
- Missing (19): `__construct`, `setFrame`, `addLabel`, `addButton`, `setLabelPosition`, `setLabelColor`, `setLabelBackground`, `setLabelFont`, `setLabelValue`, `setBarColor`, `setBarBackground`, `setBarDirection`, `getHtml`, `show`, `moveStep`, `moveNext`, `moveMin`, `hide`, `unhide`

## 2. Classes without any test

### `ACL/LoginUserStatus.php`

- Missing (3): `getMap`, `getNames`, `getValues`

### `Convert/Color/CieXyz.php`

- Missing (6): `rgbViaXyzD65ToOkLab`, `okLabViaXyzD65ToRgb`, `rgbViaXyzD65ViaXyzD50ToLab`, `labViaXyzD50ViaXyzD65ToRgb`, `okLabViaXyzD65ViaXyzD50ToLab`, `labViaXyzD50ViaXyzD65ToOkLab`

### `Convert/Color/Stringify.php`

- Missing (1): `toCssString`

### `Convert/Color/Utils.php`

- Missing (2): `compare`, `setOpacity`

### `DB/Options/Convert.php`

- Missing (2): `fromName`, `fromValue`

### `Logging/Logger/MessageLevel.php`

- Missing (5): `fromName`, `fromValue`, `includes`, `isHigherThan`, `isLowerThan`

### `Template/SmartyExtend.php`

- Missing (6): `__construct`, `setSmartyPaths`, `setSmartyVarsFrontend`, `setSmartyVarsAdmin`, `mergeCmsSmartyVars`, `renderSmarty`

### `Output/Form/TableArrays/*.php`

- Classes: `EditAccess`, `EditGroups`, `EditLanguages`, `EditMenuGroup`, `EditOrder`, `EditPages`, `EditSchemas`, `EditUsers`, `EditVisibleGroup`
- Missing in each: `__construct`, `setTableArray`
- Note: these return static table definitions, so one data provider test covering all of them would be enough

## 3. Partially tested classes

| File | Tested | Missing methods |
|---|---|---|
| `ACL/Login.php` | 29 / 44 | `__construct`, `writeLog`, `loginGetLocale`, `loginGetHeaderColor`, `loginGetPages`, `loginGetPageLookupList`, `loginPageAccessAllowed`, `loginGetEuid`, `loginGetEuCuid`, `loginGetEcuuid`, `loginGetEuCuuid`, `loginLogoutUser`, `loginGetPermissionOkay`, `loginGetEditAccessCuidFromId`, `loginGetEditAccessIdFromCuid` |
| `Check/Email.php` | 6 / 7 | `getEmailRegexCheck` |
| `Combined/ArrayHandler.php` | 24 / 25 | `arrayRemoveEntry` |
| `Convert/Color/Color.php` | 24 / 56 | `hslToOkLab`, `okLabToHsl`, `hslToOkLch`, `okLchToHsl`, `hsbToOkLab`, `okLabToHsb`, `hsbToOkLch`, `okLchToHsb`, `hwbToOkLab`, `okLabToHwb`, `hwbToOkLch`, `okLchToHwb`, `hslToLab`, `labToHsl`, `hslToLch`, `lchToHsl`, `hsbToLab`, `labToHsb`, `hsbToLch`, `lchToHsb`, `hwbToLab`, `labToHwb`, `hwbToLch`, `lchToHweb`, `okLabToLab`, `labToOkLab`, `okLchToLab`, `labToOkLch`, `okLchToLch`, `lchToOkLch`, `okLabToLch`, `lchToOkLab` |
| `Convert/Color/Coordinates/HSB.php` | 3 / 5 | `create`, `toCssString` |
| `Convert/Color/Coordinates/HSL.php` | 3 / 5 | `create`, `toCssString` |
| `Convert/Color/Coordinates/HWB.php` | 3 / 5 | `create`, `toCssString` |
| `Convert/Color/Coordinates/LCH.php` | 3 / 5 | `create`, `toCssString` |
| `Convert/Color/Coordinates/Lab.php` | 3 / 5 | `create`, `toCssString` |
| `Convert/Color/Coordinates/RGB.php` | 6 / 8 | `create`, `toCssString` |
| `Convert/Color/Coordinates/XYZ.php` | 3 / 5 | `create`, `toCssString` |
| `Convert/Json.php` | 4 / 5 | `jsonPrettyPrint` |
| `Convert/MimeAppName.php` | 2 / 3 | `__construct` |
| `Create/RandomKey.php` | 1 / 7 | `__construct`, `setRandomKeyData`, `getRandomKeyData`, `getRandomKeyDataLength`, `setRandomKeyLength`, `getRandomKeyLength` |
| `Create/Session.php` | 16 / 21 | `getSessionIdCall`, `restartSession`, `setAutoWriteClose`, `checkAutoWriteClose`, `writeClose` |
| `DB/IO.php` | 75 / 100 | `dbDumpData`, `dbCheckQueryForInsert`, `dbCheckQueryForUpdate`, `dbPrepareAsync`, `dbExecuteAsync`, `dbGetAsyncRunning`, `dbWriteData`, `dbWriteDataExt`, `dbToggleDebug`, `dbUnsetConvertFlag`, `dbResetConvertFlag`, `dbGetConvertFlag`, `dbGetConvertPlaceholder`, `dbSetConvertPlaceholderTarget`, `dbGetConvertPlaceholderTarget`, `dbSetDebugReplacePlaceholder`, `dbGetDebugReplacePlaceholder`, `dbResetEncoding`, `dbResetQuery`, `dbGetParams`, `dbResetParams`, `dbGetQueryHash`, `dbResetQueryHash`, `dbGetQueryParamPlaceholders`, `dbGetReturning` |
| `DB/Support/ConvertPlaceholder.php` | 1 / 2 | `updateParamList` |
| `Debug/LoggingLegacy.php` | 18 / 23 | `basicSetLogId`, `debugFor`, `resetErrorMsg`, `getErrorMsg`, `mergeErrors` |
| `Debug/Support.php` | 17 / 18 | `printIsoTime` |
| `Get/System.php` | 4 / 6 | `getPageNameArray`, `getIpAddresses` |
| `Language/L10n.php` | 22 / 32 | `setOverrideEncoding`, `getOverrideEncoding`, `getEncoding`, `__getLang`, `getMoFile`, `__getMoFile`, `getBaseLocalePath`, `getBaseContentPath`, `__e`, `ngettext` |
| `Language/l10n_functions.php` | 6 / 13 | `_ngettext`, `_pgettext`, `_npgettext`, `_dgettext`, `_dngettext`, `_dpgettext`, `_dnpgettext` |
| `Logging/ErrorMessage.php` | 8 / 12 | `setFlagLogError`, `getFlagLogError`, `setFlagLogWarning`, `getFlagLogWarning` |
| `Logging/Logger/Level.php` | 4 / 8 | `isHigherThan`, `isLowerThan`, `toPsrLogLevel`, `toRFC5424Level` |
| `Logging/Logging.php` | 30 / 48 | `processLogLevel`, `setLogDate`, `setErrorMessageCallSetMessage`, `setErrorMessageCallSetErrorMsg`, `prAr`, `prBl`, `dV`, `eV`, `getSetting`, `basicSetLogId`, `debugFor`, `setLogLevelAll`, `getLogLevelAll`, `setLogLevel`, `getLogLevel`, `setLogPer`, `getLogPer`, `logger2Debug` |
| `Security/CreateKey.php` | 3 / 6 | `randomKey`, `bin2hex`, `hex2bin` |
| `Template/HtmlBuilder/Block.php` | 2 / 12 | `ael`, `aelx`, `addSub`, `resetSub`, `acssel`, `rcssel`, `scssel`, `phfo`, `buildHtmlFromList`, `printHtmlFromArray` |
| `Template/HtmlBuilder/Element.php` | 20 / 25 | `setName`, `removeSub`, `addElementWithId`, `printHtmlFromObject`, `printHtmlFromArray` |
| `Template/HtmlBuilder/General/Error.php` | 4 / 6 | `setWarning`, `setError` |
| `Template/HtmlBuilder/StringReplace.php` | 1 / 7 | `loadElements`, `updateElement`, `getElement`, `setReplaceBlock`, `getReplaceBlock`, `buildElement` |
| `UrlRequests/Curl.php` | 8 / 9 | `getUrlParsedSent` |

Notes:

- `ACL/Login.php`: the constructor is only used via `getMockBuilder`; the test file header already lists part of these as "Not yet covered"
- `Convert/Color/Color.php`: round trips tested: rgb/hsl/hsb/hwb among each other, rgb<->lab, rgb<->okLab, lab<->lch, okLab<->okLch. Note: `lchToHweb` looks like a typo for `lchToHwb`
- `Convert/MimeAppName.php`: only the static methods are tested, the class is never instantiated
- `Create/RandomKey.php`: only `randomKeyGen` is tested, the instance/key range API is not
- `Debug/LoggingLegacy.php`: TODO in test: setLogUniqueId/getLogUniqueId, setLogPer log file name pattern
- `Get/System.php`: `getIpAddresses` has a test, but it only does `assertTrue(true)` (no REMOTE_ADDR in CLI)
- `Language/L10n.php`: TODO in test: domain based gettext functions
- `Logging/Logging.php`: TODO in test: setLogPer log file name pattern
- `Template/HtmlBuilder/Block.php`: the test file lists the missing methods as comments
- `UrlRequests/Curl.php`: most request tests are skipped if no test backend URL is configured

## 4. Only covered indirectly

These have no direct test. They are exercised (partly) through other classes.

| File | Via | Methods without a direct test |
|---|---|---|
| `DB/SQL/PgSQL.php` | only used through `DB\IO` (CoreLibsDBIOTest, needs the PgSQL test DB) | `__dbLastErrorQuery`, `__dbQuery`, `__dbQueryParams`, `__dbSendQuery`, `__dbSendQueryParams`, `__dbGetResult`, `__dbClose`, `__dbPrepare`, `__dbExecute`, `__dbSendPrepare`, `__dbSendExecute`, `__dbNumRows`, `__dbNumFields`, `__dbFieldName`, `__dbFieldType`, `__dbFetchArray`, `__dbResultType`, `__dbFetchAll`, `__dbAffectedRows`, `__dbInsertId`, `__dbPrimaryKey`, `__dbConnect`, `__dbPrintLastError`, `__dbPrintError`, `__dbMetaData`, `__dbEscapeString`, `__dbEscapeLiteral`, `__dbEscapeIdentifier`, `__dbEscapeBytea`, `__dbUnescapeBytea`, `__dbConnectionBusy`, `__dbConnectionBusySocketWait`, `__dbVersionInfo`, `__dbVersionInfoParameterList`, `__dbVersion`, `__dbVersionNumeric`, `__dbArrayParse`, `__dbParameter`, `__dbShow`, `__dbSetSchema`, `__dbGetSchema`, `__dbSetEncoding`, `__dbGetEncoding`, `__dbGetQueryParams` |
| `Language/Core/GetTextReader.php` | only used through `L10n` (CoreLibsLanguageL10nTest) | `read`, `readintarray`, `__construct`, `getShortCircuit`, `getEnableCache`, `translate`, `ngettext`, `pgettext`, `npgettext` |
| `Language/Core/FileReader.php` | only used through `GetTextReader` / `L10n` | `__construct`, `read`, `seekto`, `currentpos`, `length`, `close` |
| `Language/Core/CachedFileReader.php` | only used through `GetTextReader` / `L10n` | `__construct` |
| `Language/Core/StreamReader.php` | base class of the readers, only used through `L10n` | `__construct`, `read`, `seekto`, `currentpos`, `length` |
| `Language/Core/StringReader.php` | not used by `L10n`, so no coverage at all | `__construct`, `read`, `seekto`, `currentpos`, `length` |

## 5. Deprecated code without tests

Low priority. Only add tests here if the code is not going to be removed.

- `Basic.php` (73 methods): class is mostly deprecated (methods moved to their own classes)
- `DeprecatedHelper/Deprecated84.php` (3 methods): wrappers for the PHP 8.4 `$escape` deprecation in fputcsv/fgetcsv/str_getcsv

## 6. Tests that are skipped depending on the environment

These tests exist, but they only run if the environment is set up. If it isn't, the methods are not tested.

| Test file | Requirement |
|---|---|
| `DB/CoreLibsDBIOTest.php` | `pgsql` extension and the test database |
| `ACL/CoreLibsACLLoginTest.php` | `pgsql` extension, test database, ACL Login SQL load file |
| `UrlRequests/CoreLibsUrlRequestsCurlTest.php` | a test backend URL (`url_basic`), otherwise all request/exception tests are skipped |
| `Create/CoreLibsCreateSessionTest.php` | `testMethodSetGet`, `testMany` and `testUnsetAll` are skipped in CLI |
| `Debug/CoreLibsDebugFileWriterTest.php` | `/tmp` folder |
| `Get/CoreLibsGetDotEnvTest.php` | unreadable file case is skipped when running as root |

## 7. Open TODOs inside existing tests

- `ACL/CoreLibsACLLoginTest.php:1082`: Test that if we have n day check with login, that after login we can use parameter login again
- `ACL/CoreLibsACLLoginTest.php:1595`: detail match of ACL array (loginGetAcl)
- `Check/CoreLibsCheckColorsTest.php:137`: check all % and non percent combinations
- `Check/CoreLibsCheckColorsTest.php:176`: check all % and non percent combinations
- `Check/CoreLibsCheckColorsTest.php:182`: check all % and non percent combinations with percent transparent
- `Check/CoreLibsCheckColorsTest.php:272`: other combined flag checks all combinations
- `Check/CoreLibsCheckEmailTest.php:253`: add more test emails here
- `Check/CoreLibsCheckPhpVersionTest.php:48`: add null tests
- `Combined/CoreLibsCombinedDateTimeTest.php:310`: create unit tests for ALL edge cases
- `Convert/CoreLibsConvertColorsTest.php:291`: add cross convert check
- `DB/CoreLibsDBIOTest.php:513`: error checks
- `DB/CoreLibsDBIOTest.php:674`: special test with null call too
- `DB/CoreLibsDBIOTest.php:1106`: Load current settings from DB and ajust comapre string
- `DB/CoreLibsDBIOTest.php:1571`: failed db connection
- `DB/CoreLibsDBIOTest.php:1573`: connection busy
- `DB/CoreLibsDBIOTest.php:3499`: no db connection test
- `DB/CoreLibsDBIOTest.php:3501`: connection busy test
- `DB/CoreLibsDBIOTest.php:3503`: query could not be prepared test
- `DB/CoreLibsDBIOTest.php:3581`: execute does not return a result
- `DB/CoreLibsDBIOTest.php:3582`: prepared statement with placeholder params auto convert
- `DB/CoreLibsDBIOTest.php:4203`: add encoding changed test with DB insert
- `DB/CoreLibsDBIOTest.php:5507`: test with multiple entries
- `DB/CoreLibsDBIOTest.php:5508`: test with same entry ($1, $1, :var, :var)
- `DB/CoreLibsDBIOTest.php:5601`: other way around for to pdo
- `DB/CoreLibsDBIOTest.php:5604`: implement below checks
- `Debug/CoreLibsDebugLoggingLegacyTest.php:9`: setLogPer test log file written matches pattern
- `Debug/CoreLibsDebugLoggingLegacyTest.php:877`: more tests with different error messages
- `Debug/CoreLibsDebugLoggingLegacyTest.php:992`: as printing directly is not really done anymore tests below are todo
- `Debug/CoreLibsDebugLoggingLegacyTest.php:1006`: setLogUniqueId/getLogUniqueId
- `Debug/CoreLibsDebugMemoryUsageTest.php:114`: additional tests with use more memory and check diff matching
- `Debug/CoreLibsDebugMemoryUsageTest.php:115`: reset memory usage test
- `Language/CoreLibsLanguageL10nTest.php:581`: domain based
- `Language/CoreLibsLanguageL10nTest.php:1102`: others d/dn/dp/dnp gettext functions
- `Logging/CoreLibsLoggingLoggingTest.php:11`: setLogPer test log file written matches pattern
- `UrlRequests/CoreLibsUrlRequestsCurlTest.php:1183`: Exception:CurlInitError

## 8. Fully covered (all public methods are called from a test)

`Check/Colors.php`, `Check/Encoding.php`, `Check/File.php`, `Check/Password.php`, `Check/PhpVersion.php`, `Combined/DateTime.php`, `Convert/Byte.php`, `Convert/Colors.php`, `Convert/Encoding.php`, `Convert/Html.php`, `Convert/Math.php`, `Convert/MimeEncode.php`, `Convert/SetVarType.php`, `Convert/SetVarTypeNull.php`, `Convert/Strings.php`, `Create/Crc64.php`, `Create/Email.php`, `Create/Hash.php`, `Create/Uids.php`, `Debug/FileWriter.php`, `Debug/Logging.php`, `Debug/MemoryUsage.php`, `Debug/RunningTime.php`, `Get/DotEnv.php`, `Language/GetLocale.php`, `Logging/Logger/Flag.php`, `Output/Image.php`, `Security/AsymmetricAnonymousEncryption.php`, `Security/Password.php`, `Security/SymmetricEncryption.php`, `UrlRequests/CurlTrait.php`

## Suggested priority

1. **Small and easy to test**: `Combined/ArrayHandler::arrayRemoveEntry`, `Convert/Json::jsonPrettyPrint`, `Check/Email::getEmailRegexCheck`, `Debug/Support::printIsoTime`, `Security/CreateKey` (`randomKey`, `bin2hex`, `hex2bin`), `Logging/Logger/Level` + `MessageLevel` enum methods, `DB/Options/Convert` (`fromName`, `fromValue`), `ACL/LoginUserStatus`, `Convert/Color/Utils`, `Convert/Color/Stringify`, `create`/`toCssString` on all color coordinates
2. **Pure logic, but larger**: `Convert/Color/CieXyz`, the missing `Convert/Color/Color` conversions, `Template/HtmlBuilder/Block` and `StringReplace`, `Output/Form/Token`, `Output/ProgressBar`, `DeprecatedHelper/Deprecated84`
3. **Needs the DB/environment setup**: `DB/IO` missing methods, `DB/Extended/ArrayIO`, `ACL/Login` getters, `Admin/Backend`, `Admin/EditBase`, `Output/Form/Generate`, `Template/SmartyExtend`
