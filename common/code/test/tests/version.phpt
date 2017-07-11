<?php

use Tester\Assert;

require_once(__DIR__.'/../config/bootstrap.php');
require_once(__DIR__.'/../../boost.php');

$develop = BoostVersion::develop();
$master = BoostVersion::master();
$boost_1_55_0 = BoostVersion::release(1, 55, 0);
$boost_1_54_0 = BoostVersion::release(1, 54, 0);
$boost_1_56_0_b1_rc1 = BoostVersion::release(1, 56, 0, array('beta' => 1, 'rc' => 1));
$boost_1_56_0_b1 = BoostVersion::release(1, 56, 0, 1);
$boost_1_56_0_b2_rc1 = BoostVersion::release(1, 56, 0, array('beta' => 2, 'rc' => 1));
$boost_1_56_0_b2_rc2 = BoostVersion::release(1, 56, 0, array('beta' => 2, 'rc' => 2));
$boost_1_56_0_b2 = BoostVersion::release(1, 56, 0, array('beta' => 2));
$boost_1_56_0_rc1 = BoostVersion::release(1, 56, 0, array('rc' => 1));
$boost_1_56_0_rc2 = BoostVersion::release(1, 56, 0, array('rc' => 2));
$boost_1_56_0 = BoostVersion::release(1, 56, 0);

Assert::true($develop->compare($master) > 0);
Assert::true($master->compare($develop) < 0);
Assert::true($develop->compare($boost_1_55_0) > 0);
Assert::true($boost_1_55_0->compare($develop) < 0);
Assert::true($boost_1_55_0->compare($boost_1_54_0) > 0);
Assert::true($boost_1_54_0->compare($boost_1_55_0) < 0);

Assert::same($boost_1_55_0->compare('1_55_0'), 0);
Assert::true($boost_1_55_0->compare('1_54_0') > 0);
Assert::true($boost_1_55_0->compare('1_56_0') < 0);

Assert::same($develop->dir(), 'develop');
Assert::same($master->dir(), 'master');
Assert::same($boost_1_55_0->dir(), 'boost_1_55_0');
Assert::same((string) $boost_1_55_0, '1.55.0');

Assert::same($boost_1_56_0_b1_rc1->compare($boost_1_56_0_b1_rc1), 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0_b1) < 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0_b2_rc1) < 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0_b2_rc2) < 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0_b2) < 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0_rc1) < 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0_rc2) < 0);
Assert::true($boost_1_56_0_b1_rc1->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_b1_rc1) > 0);
Assert::same($boost_1_56_0_b1->compare($boost_1_56_0_b1), 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_b2_rc1) < 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_b2_rc2) < 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_b2) < 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_rc1) < 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0_rc2) < 0);
Assert::true($boost_1_56_0_b1->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0_b1) > 0);
Assert::same($boost_1_56_0_b2_rc1->compare($boost_1_56_0_b2_rc1), 0);
Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0_b2_rc2) < 0);
Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0_b2) < 0);
Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0_rc1) < 0);
Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0_rc2) < 0);
Assert::true($boost_1_56_0_b2_rc1->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0_b1_rc1) > 0);
Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0_b2_rc1) > 0);
Assert::same($boost_1_56_0_b2_rc2->compare($boost_1_56_0_b2_rc2), 0);
Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0_b2) < 0);
Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0_rc1) < 0);
Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0_rc2) < 0);
Assert::true($boost_1_56_0_b2_rc2->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_b1_rc1) > 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_b2_rc1) > 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_b2_rc2) > 0);
Assert::same($boost_1_56_0_b2->compare($boost_1_56_0_b2), 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_rc1) < 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0_rc2) < 0);
Assert::true($boost_1_56_0_b2->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0_b1_rc1) > 0);
Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0_b2_rc1) > 0);
Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0_b2_rc2) > 0);
Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0_b2) > 0);
Assert::same($boost_1_56_0_rc1->compare($boost_1_56_0_rc1), 0);
Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0_rc2) < 0);
Assert::true($boost_1_56_0_rc1->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0_b1_rc1) > 0);
Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0_b2_rc1) > 0);
Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0_b2_rc2) > 0);
Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0_b2) > 0);
Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0_rc1) > 0);
Assert::same($boost_1_56_0_rc2->compare($boost_1_56_0_rc2), 0);
Assert::true($boost_1_56_0_rc2->compare($boost_1_56_0) < 0);

Assert::true($boost_1_56_0->compare($boost_1_56_0_b1_rc1) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_b1) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_b2_rc1) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_b2_rc2) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_b2) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_rc1) > 0);
Assert::true($boost_1_56_0->compare($boost_1_56_0_rc2) > 0);
Assert::same($boost_1_56_0->compare($boost_1_56_0), 0);

Assert::same($boost_1_56_0_b1_rc1->compare('1_56_0beta_rc'), 0);
Assert::same($boost_1_56_0_b1_rc1->compare('1_56_0b1.rc'), 0);
Assert::same($boost_1_56_0_b1_rc1->compare('1_56_0_b rc1'), 0);
Assert::same($boost_1_56_0_b1_rc1->compare('1_56_0_beta1_rc1'), 0);

Assert::same($boost_1_56_0_b1->compare('1_56_0beta'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0b1'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0_b1'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0_beta1'), 0);
Assert::same($boost_1_56_0_b1->compare('1_56_0_beta'), 0);

Assert::same($boost_1_56_0_b2->compare('1_56_0b2'), 0);
Assert::same($boost_1_56_0_b2->compare('1_56_0_b2'), 0);
Assert::same($boost_1_56_0_b2->compare('1_56_0_beta2'), 0);
Assert::same($boost_1_56_0_b2->compare('1_56_0_beta_2'), 0);
Assert::same($boost_1_56_0_b2->compare('1.56.0 beta 2'), 0);

Assert::same($boost_1_56_0_b2_rc1->compare('1_56_0beta2_rc1'), 0);
Assert::same($boost_1_56_0_b2_rc1->compare('1_56_0b2.rc1'), 0);
Assert::same($boost_1_56_0_b2_rc1->compare('1_56_0_b2 rc1'), 0);
Assert::same($boost_1_56_0_b2_rc1->compare('1_56_0_beta2_rc1'), 0);

Assert::same($boost_1_56_0_rc1->compare('1_56_0rc'), 0);
Assert::same($boost_1_56_0_rc1->compare('1.56.0.rc1'), 0);

Assert::same($boost_1_55_0->git_ref(), 'boost-1.55.0');
Assert::same($boost_1_56_0_b1->git_ref(), 'boost-1.56.0-beta1');

Assert::false($boost_1_56_0_b1_rc1->is_final_release());
Assert::false($boost_1_56_0_b1->is_final_release());
Assert::false($boost_1_56_0_b2_rc1->is_final_release());
Assert::false($boost_1_56_0_b2_rc2->is_final_release());
Assert::false($boost_1_56_0_b2->is_final_release());
Assert::false($boost_1_56_0_rc1->is_final_release());
Assert::false($boost_1_56_0_rc2->is_final_release());
Assert::true($boost_1_56_0->is_final_release());
