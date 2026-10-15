@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Testing Policy

- DO NOT write automated tests unless explicitly requested by the user.
- Focus strictly on implementing the requested features, business logic, models, and Filament components.
- Rely on manual verification and database integrity unless told to test critical manufacturing/inventory logic.
- When tests ARE requested, this project uses Pest. Create tests: `{{ $assist->artisanCommand('make:test --pest {name}') }}`.
- Run tests: `{{ $assist->artisanCommand('test --compact') }}` or filter: `{{ $assist->artisanCommand('test --compact --filter=testName') }}`.
