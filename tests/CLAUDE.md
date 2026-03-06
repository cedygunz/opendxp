# Tests — Claude Instructions

## Framework & Setup

Tests use **Codeception**. Configuration: `codeception.dist.yml` in the repository root.

## Directory Structure

```
tests/
├── Cache/                        # Cache integration tests
├── Model/                        # Model integration tests (need DB + running OpenDXP)
│   ├── Asset/
│   ├── DataObject/
│   ├── DataType/
│   ├── Document/
│   ├── Element/
│   ├── Inheritance/
│   ├── LazyLoading/
│   ├── Permissions/
│   ├── Relations/
│   ├── Tool/
│   └── WebsiteSetting/
├── Service/                      # Service integration tests
├── Twig/                         # Twig extension tests
├── Unit/                         # Unit tests (no DB, no OpenDXP bootstrap)
│   ├── Document/
│   ├── Helper/
│   └── HttpKernel/
└── Support/
    ├── Helper/                   # Codeception module helpers
    ├── Resources/                # Test fixtures (assets, dummy files)
    ├── Test/
    │   ├── ModelTestCase.php     # Base for integration tests
    │   └── TestCase.php          # Base for unit tests
    └── Util/
```

## Base Classes

| Situation                                   | Base Class                                 |
|---------------------------------------------|--------------------------------------------|
| Test needs DB, models, real OpenDXP objects | `OpenDxp\Tests\Support\Test\ModelTestCase` |
| Pure logic, no I/O, no DB                   | `OpenDxp\Tests\Support\Test\TestCase`      |

**Rule of thumb:**
- New model behaviour, relations, permissions, data types → `ModelTestCase`
- New helper, utility, Twig extension, pure service → `TestCase`

## Suites

| Suite     | Base Class      | DB needed | Typical content                             |
|-----------|-----------------|-----------|---------------------------------------------|
| `Unit`    | `TestCase`      | No        | Helpers, utilities, pure logic              |
| `Model`   | `ModelTestCase` | Yes       | Documents, Assets, DataObjects, permissions |
| `Cache`   | `ModelTestCase` | Yes       | Cache layer behaviour                       |
| `Service` | `ModelTestCase` | Yes       | Service-level integration                   |
| `Twig`    | `ModelTestCase` | Yes       | Twig extensions rendering                   |

## Running Tests

### Prerequisites
First time starts: Is the MCP tool `opendxp-testkit` available in this session?

**No → Run setup now:**

Ask the developer this question and wait for the answer:
> "Where is your local `docker-testkit` directory? (absolute path)"

Once the path is provided:

- Write `.mcp.json` in the repository root:

```json
{
    "mcpServers": {
        "opendxp-testkit": {
            "type": "stdio",
            "command": "node",
            "args": [
                "<PATH>/mcp-server/index.js"
            ]
        }
    }
}
```

- Add `.mcp.json` to `.gitignore` if not already present
- Tell the developer: "Please restart Claude Code — `opendxp-testkit` will be available after restart."
- Stop. Wait for restart.

**Yes → Normal workflow:**

---

### Workflow: Running tests

#### Step 1 — Check status

Call `get_status()`. The result shows:

- `Configured bundle` — which bundle is currently set in the testkit
- `ddev running` — whether ddev is running

Decide based on the result:

| Situation                            | Action                                                                                   |
|--------------------------------------|------------------------------------------------------------------------------------------|
| `ddev running: false`                | Call `set_bundle("BUNDLE_DIR_NAME")` → ddev will be started → use `with_composer=true`   |
| `ddev running: true`, wrong bundle   | Call `set_bundle("BUNDLE_DIR_NAME")` → ddev will be restarted → use `with_composer=true` |
| `ddev running: true`, correct bundle | Proceed to step 2 — no `with_composer` needed                                            |

`BUNDLE_DIR_NAME` = `basename` of this directory (e.g. `opendxp`)

> **Note on rsync:** Files are **always** synced into the container via rsync — no ddev restart is needed after code changes.

#### Step 2 — Run tests

```
run_codeception(test_path="tests/...", with_composer=true/false)
```

- Only set `test_path` when the user specifies a particular test, otherwise omit it (runs all tests)
- Do **not** set `debug` by default (see rules below)

**`test_path` formats:**
| What the user wants | `test_path` value |
|---|---|
| All tests | *(omit)* |
| A specific test class | `tests/Unit/Helper/SomeHelperTest.php` |
| A specific test folder | `tests/Unit/Document` |
| All unit tests | `tests/Unit` |
| All model/integration tests | `tests/Model` |
| All cache tests | `tests/Cache` |
| All service tests | `tests/Service` |
| All Twig tests | `tests/Twig` |

#### Step 3 — On test failure

If tests fail, ask the user:
> "Tests failed. Should I re-run with `--debug` for detailed output?"

Only if the user agrees: `run_codeception(debug=true, ...)`

---

### Workflow: PHPStan

`run_phpstan()` is a standalone task — only run it when the user explicitly asks for it.

```
run_phpstan(level=6)    ← default level, adjust on request
```

---

### Rules

- Write code and tests in this directory only — never touch `app/` inside the testkit
- `with_composer=true` after `set_bundle` calls or if something changed in `composer.json`
- `debug=true` only with explicit user consent after a failed test run
- PHPStan only on explicit request

---

### Fallback: Without MCP

Only use these commands if `opendxp-testkit` is not available in this session:

```bash
# all suites
vendor/bin/codecept run

# specific suite
vendor/bin/codecept run Unit
vendor/bin/codecept run Model

# single file
vendor/bin/codecept run Unit tests/Unit/Helper/SomeHelperTest.php
```