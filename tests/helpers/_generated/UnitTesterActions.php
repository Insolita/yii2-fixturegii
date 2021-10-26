<?php  //[STAMP] 22f2a4467ba66d83c420a036a75a0318
namespace tests\_generated;

// This class was automatically generated by build task
// You should not change it manually as it will be overwritten on next build
// @codingStandardsIgnoreFile

trait UnitTesterActions
{
    /**
     * @return \Codeception\Scenario
     */
    abstract protected function getScenario();

    
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Creates and loads fixtures from a config.
     * The signature is the same as for the `fixtures()` method of `yii\test\FixtureTrait`
     *
     * ```php
     * <?php
     * $I->haveFixtures([
     *     'posts' => PostsFixture::className(),
     *     'user' => [
     *         'class' => UserFixture::className(),
     *         'dataFile' => '@tests/_data/models/user.php',
     *      ],
     * ]);
     * ```
     *
     * Note: if you need to load fixtures before a test (probably before the
     * cleanup transaction is started; `cleanup` option is `true` by default),
     * you can specify the fixtures in the `_fixtures()` method of a test case
     *
     * ```php
     * <?php
     * // inside Cest file or Codeception\TestCase\Unit
     * public function _fixtures(){
     *     return [
     *         'user' => [
     *             'class' => UserFixture::className(),
     *             'dataFile' => codecept_data_dir() . 'user.php'
     *         ]
     *     ];
     * }
     * ```
     * instead of calling `haveFixtures` in Cest `_before`
     *
     * @param $fixtures
     * @part fixtures
     * @see \Codeception\Module\Yii2::haveFixtures()
     */
    public function haveFixtures($fixtures) {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('haveFixtures', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns all loaded fixtures.
     * Array of fixture instances
     *
     * @part fixtures
     * @return array
     * @see \Codeception\Module\Yii2::grabFixtures()
     */
    public function grabFixtures() {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('grabFixtures', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Gets a fixture by name.
     * Returns a Fixture instance. If a fixture is an instance of
     * `\yii\test\BaseActiveFixture` a second parameter can be used to return a
     * specific model:
     *
     * ```php
     * <?php
     * $I->haveFixtures(['users' => UserFixture::className()]);
     *
     * $users = $I->grabFixture('users');
     *
     * // get first user by key, if a fixture is an instance of ActiveFixture
     * $user = $I->grabFixture('users', 'user1');
     * ```
     *
     * @param $name
     * @return mixed
     * @throws ModuleException if the fixture is not found
     * @part fixtures
     * @see \Codeception\Module\Yii2::grabFixture()
     */
    public function grabFixture($name, $index = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('grabFixture', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Inserts a record into the database.
     *
     * ``` php
     * <?php
     * $user_id = $I->haveRecord('app\models\User', array('name' => 'Davert'));
     * ?>
     * ```
     *
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part orm
     * @see \Codeception\Module\Yii2::haveRecord()
     */
    public function haveRecord($model, $attributes = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('haveRecord', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Checks that a record exists in the database.
     *
     * ``` php
     * $I->seeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     * @see \Codeception\Module\Yii2::seeRecord()
     */
    public function seeRecord($model, $attributes = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\Assertion('seeRecord', func_get_args()));
    }
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * [!] Conditional Assertion: Test won't be stopped on fail
     * Checks that a record exists in the database.
     *
     * ``` php
     * $I->seeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     * @see \Codeception\Module\Yii2::seeRecord()
     */
    public function canSeeRecord($model, $attributes = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion('seeRecord', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Checks that a record does not exist in the database.
     *
     * ``` php
     * $I->dontSeeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     * @see \Codeception\Module\Yii2::dontSeeRecord()
     */
    public function dontSeeRecord($model, $attributes = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('dontSeeRecord', func_get_args()));
    }
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * [!] Conditional Assertion: Test won't be stopped on fail
     * Checks that a record does not exist in the database.
     *
     * ``` php
     * $I->dontSeeRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @part orm
     * @see \Codeception\Module\Yii2::dontSeeRecord()
     */
    public function cantSeeRecord($model, $attributes = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion('dontSeeRecord', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Retrieves a record from the database
     *
     * ``` php
     * $category = $I->grabRecord('app\models\User', array('name' => 'davert'));
     * ```
     *
     * @param $model
     * @param array $attributes
     * @return mixed
     * @part orm
     * @see \Codeception\Module\Yii2::grabRecord()
     */
    public function grabRecord($model, $attributes = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('grabRecord', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Checks that an email is sent.
     *
     * ```php
     * <?php
     * // check that at least 1 email was sent
     * $I->seeEmailIsSent();
     *
     * // check that only 3 emails were sent
     * $I->seeEmailIsSent(3);
     * ```
     *
     * @param int $num
     * @throws ModuleException
     * @part email
     * @see \Codeception\Module\Yii2::seeEmailIsSent()
     */
    public function seeEmailIsSent($num = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\Assertion('seeEmailIsSent', func_get_args()));
    }
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * [!] Conditional Assertion: Test won't be stopped on fail
     * Checks that an email is sent.
     *
     * ```php
     * <?php
     * // check that at least 1 email was sent
     * $I->seeEmailIsSent();
     *
     * // check that only 3 emails were sent
     * $I->seeEmailIsSent(3);
     * ```
     *
     * @param int $num
     * @throws ModuleException
     * @part email
     * @see \Codeception\Module\Yii2::seeEmailIsSent()
     */
    public function canSeeEmailIsSent($num = null) {
        return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion('seeEmailIsSent', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Checks that no email was sent
     *
     * @part email
     * @see \Codeception\Module\Yii2::dontSeeEmailIsSent()
     */
    public function dontSeeEmailIsSent() {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('dontSeeEmailIsSent', func_get_args()));
    }
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * [!] Conditional Assertion: Test won't be stopped on fail
     * Checks that no email was sent
     *
     * @part email
     * @see \Codeception\Module\Yii2::dontSeeEmailIsSent()
     */
    public function cantSeeEmailIsSent() {
        return $this->getScenario()->runStep(new \Codeception\Step\ConditionalAssertion('dontSeeEmailIsSent', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns array of all sent email messages.
     * Each message implements the `yii\mail\MessageInterface` interface.
     * Useful to perform additional checks using the `Asserts` module:
     *
     * ```php
     * <?php
     * $I->seeEmailIsSent();
     * $messages = $I->grabSentEmails();
     * $I->assertEquals('admin@site,com', $messages[0]->getTo());
     * ```
     *
     * @part email
     * @return array
     * @throws ModuleException
     * @see \Codeception\Module\Yii2::grabSentEmails()
     */
    public function grabSentEmails() {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('grabSentEmails', func_get_args()));
    }

 
    /**
     * [!] Method is generated. Documentation taken from corresponding module.
     *
     * Returns the last sent email:
     *
     * ```php
     * <?php
     * $I->seeEmailIsSent();
     * $message = $I->grabLastSentEmail();
     * $I->assertEquals('admin@site,com', $message->getTo());
     * ```
     * @part email
     * @see \Codeception\Module\Yii2::grabLastSentEmail()
     */
    public function grabLastSentEmail() {
        return $this->getScenario()->runStep(new \Codeception\Step\Action('grabLastSentEmail', func_get_args()));
    }
}