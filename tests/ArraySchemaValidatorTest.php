<?hh // strict

namespace Slack\Hack\JsonSchema\Tests;

use namespace HH\Lib\{C, Str};
use function Facebook\FBExpect\expect;

use type Slack\Hack\JsonSchema\Tests\Generated\ArraySchemaValidator;

final class ArraySchemaValidatorTest extends BaseCodegenTestCase {

  <<__Override>>
  public static async function beforeFirstTestAsync(): Awaitable<void> {
    $ret = self::getBuilder('array-schema.json', 'ArraySchemaValidator');
    $ret['codegen']->build();
    require_once($ret['path']);
  }

  public function testArrayOfStringsInvalidRoot(): void {
    $validator = new ArraySchemaValidator(varray['test', 'list', 'of', 'strings']);
    $validator->validate();

    expect($validator->isValid())->toBeFalse();
  }

  public function testArrayOfStringsValid(): void {
    $validator = new ArraySchemaValidator(dict[
      'array_of_strings' => vec['test', 'list', 'of', 'strings'],
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();
    expect(C\count($validated['array_of_strings'] ?? vec[]))->toBeSame(4);
  }

  public function testArrayOfStringsLegacyArrays(): void {
    $validator = new ArraySchemaValidator(darray['array_of_strings' => varray['test', 'list', 'of', 'strings']]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();
    expect(C\count($validated['array_of_strings'] ?? vec[]))->toBeSame(4);
  }

  public function testArrayOfStringsLegacyAndHackArrays(): void {
    $validator = new ArraySchemaValidator(dict[
      'array_of_strings' => varray['test', 'list', 'of', 'strings'],
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();
    expect(C\count($validated['array_of_strings'] ?? vec[]))->toBeSame(4);
  }

  public function testUntypedArrayValid(): void {
    $validator = new ArraySchemaValidator(dict[
      'untyped_array' => varray['test', 'values'],
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();

    expect(Shapes::idx($validated, 'untyped_array') as nonnull[0])->toBeSame('test');
    expect(Shapes::idx($validated, 'untyped_array') as nonnull[1])->toBeSame('values');
  }

  public function testCoerceArrayValidString(): void {
    $input = vec[1, 2, 3, 4];

    $validator = new ArraySchemaValidator(dict[
      'coerce_array' => \json_encode($input),
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();

    expect($validated)->toBeSame(shape('coerce_array' => $input));
  }

  public function testCoerceArrayInvalidString(): void {
    $input = vec[1, 2, 3, 'invalid'];

    $validator = new ArraySchemaValidator(dict[
      'coerce_array' => \json_encode($input),
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeFalse();
  }

  public function testCoerceArrayBadString(): void {
    $validator = new ArraySchemaValidator(dict[
      'coerce_array' => '{"test":',
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeFalse();
  }

  public function testCoerceArrayURLEncodedString(): void {
    $input = vec[1, 2, 3];

    $validator = new ArraySchemaValidator(dict[
      'coerce_array' => Str\join($input, ','),
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();

    expect($validated)->toBeSame(shape('coerce_array' => $input));
  }

  public function testCoerceArrayURLEncodedStringSingleValue(): void {
    $input = vec[1];

    $validator = new ArraySchemaValidator(dict[
      'coerce_array' => Str\join($input, ','),
    ]);
    $validator->validate();

    expect($validator->isValid())->toBeTrue();
    $validated = $validator->getValidatedInput();

    expect($validated)->toBeSame(shape('coerce_array' => $input));
  }

}
