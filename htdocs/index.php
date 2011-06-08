<?php

namespace Foomo;

Frontend::setUpToolbox('Testrunner');

echo MVC::run('Foomo\\TestRunner\\Frontend');
