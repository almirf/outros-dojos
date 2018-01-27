<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    /**
     * @ AfterScenario @javascript
     * @param AfterScenarioScope $scope
     */
    public function screenshotOnFailure(AfterScenarioScope $scope)
    {
        if ($scope->getTestResult()->isPassed() === false) {
            $imageData = $this->getSession()->getDriver()->getScreenshot();
            $imagePath = time() . '.png';
            file_put_contents($imagePath, $imageData);
        }
    }

    /**
     * Returns list of definition translation resources paths.
     *
     * @return array
     */
    public static function getTranslationResources() {
        return array_merge(self::getMinkTranslationResources(), glob(__DIR__ . '/../../i18n/*.xliff'));
    }

    /**
     * @When /^(?:|I )click in element "(?P<element>(?:[^"]|\\")*)"$/
     */
    public function clickInElement($element)
    {
        $session = $this->getSession();
        
        $locator = $this->fixStepArgument($element);
        $xpath = $session->getSelectorsHandler()->selectorToXpath('css', $locator);
        $element = $this->getSession()->getPage()->find(
            'xpath',
            $xpath
            );
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not find element'));
        }
        $element->click();
    }
    /**
     * @Given /^Eu devo aguardar (\d+) segundos ou pelo javascript "([^"]*)"$/
     */
    public function euDevoAguardarSegundos($segundos, $javascript)
    {
        $this->getSession()->wait($segundos * 1000, $javascript);
    }
    
    /**
     * @When /^(?:|I )wait for element "(?P<element>(?:[^"]|\\")*)" to appear$/
     * @Then /^(?:|I )should see element "(?P<element>(?:[^"]|\\")*)" appear$/
     * @param $element
     * @throws \Exception
     */
    public function iWaitForElementToAppear($element)
    {
        $this->spin(function(FeatureContext $context) use ($element) {
            try {
                $context->assertElementOnPage($element);
                return true;
            }
            catch(ResponseTextException $e) {
                // NOOP
            }
            return false;
        });
    }
    
    /**
     * @When /^(?:|I )wait for element :element to disappear$/
     * @Then /^(?:|I )should see element :element disappear$/
     * @param $element
     * @throws \Exception
     */
    public function iWaitForElementToDisappear($element)
    {
        $this->spin(function(FeatureContext $context) use ($element) {
            try {
                $context->assertElementOnPage($element);
            }
            catch(ResponseTextException $e) {
                return true;
            }
            return false;
        });
    }
    
    /**
     * @When /^(?:|I )wait for text "(?P<text>(?:[^"]|\\")*)" to appear$/
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" appear$/
     * @param $text
     * @throws \Exception
     */
    public function iWaitForTextToAppear($text)
    {
        $this->spin(function(FeatureContext $context) use ($text) {
            try {
                $context->assertPageContainsText($text);
                return true;
            }
            catch(ResponseTextException $e) {
                // NOOP
            }
            return false;
        });
    }
    
    /**
     * @When /^(?:|I )wait for text :text to disappear$/
     * @Then /^(?:|I )should see :text disappear$/
     * @param $text
     * @throws \Exception
     */
    public function iWaitForTextToDisappear($text)
    {
        $this->spin(function(FeatureContext $context) use ($text) {
            try {
                $context->assertPageContainsText($text);
            }
            catch(ResponseTextException $e) {
                return true;
            }
            return false;
        });
    }
    
    /**
     * Based on Behat's own example
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     * @param $lambda
     * @param int $wait
     * @throws \Exception
     */
    public function spin($lambda, $wait = 60)
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime)
        {
            try {
                if ($lambda($this)) {
                    return;
                }
            } catch (\Exception $e) {
                // do nothing
            }
            
            usleep(250000);
        }
        
        throw new \Exception("Spin function timed out after {$wait} seconds");
    }
    
    /**
     * @Given envio email para :arg1
     */
    public function envioEmailPara($arg1)
    {
        mail($arg1, '12345', 'bla');
    }
    /**
     * @Given clico na lixeirinha
     */
    public function clicoNaLixeirinha()
    {
        $session = $this->getSession();
        $xpath = $session->getSelectorsHandler()->selectorToXpath('css', '#trash_but');
        $element = $this->getSession()->getPage()->find(
            'xpath',
            $xpath
        );
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not find element'));
        }
        $element->click();
        $this->getSession()->getPage()->pressButton('trash_but');
    }
}
