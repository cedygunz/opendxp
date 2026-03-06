# Testing with AI (Claude)

OpenDXP ships with a `tests/CLAUDE.md` file that teaches [Claude Code](https://claude.ai/code) everything it needs to know about the test setup — naming conventions, base classes, suites, and how to run tests.

Combined with the [OpenDXP Docker Testkit](https://github.com/open-dxp/docker-testkit), Claude can **write, execute, fix, and re-run tests** in a single conversation, without you ever leaving the terminal.

---

## Prerequisites

1. **Claude Code** — `npm install -g @anthropic/claude-code`
2. **OpenDXP Docker Testkit** — clone and follow the setup in the [docker-testkit README](https://github.com/open-dxp/docker-testkit)

That's it. The rest is handled automatically.

---

## First-time setup (one conversation)

Start Claude Code in this repository:

```bash
claude
```

The first time you ask Claude to run tests, it will notice that the testkit MCP server is not yet configured and guide you through a short setup:

```
Claude:  Where is your local `docker-testkit` directory? (absolute path)

You:     /home/yourname/Projects/docker-testkit

Claude:  Done. Please restart Claude Code — `opendxp-testkit` will be
         available after restart.
```

Claude writes the `.mcp.json` for you (already git-ignored) and tells you to restart. After that, the testkit tools are available in every future session.

---

## How it works

Once connected, Claude has access to three testkit tools it uses autonomously:

| Tool | What it does |
|---|---|
| `get_status` | Checks ddev status and which bundle is active |
| `set_bundle` | Switches the testkit to a specific bundle |
| `run_codeception` | Runs Codeception tests inside the ddev container |

Claude reads `tests/CLAUDE.md` to decide where to place new tests, which base class to use, and how to structure the file — so everything fits the existing codebase.

---

## Writing and running tests

Just describe what you want covered:

```
write tests for the Site model — custom settings and domain matching
```

Claude will:

1. Read the relevant model and existing tests for context
2. Propose what it plans to cover and ask for confirmation
3. Create the test file in the right location
4. Run it immediately via the testkit
5. Fix any failures and re-run until everything is green

---

## Example conversation

The following is a real example from this codebase — tests for the `Site` model after the `customSettings` feature was added.

---

**You:**
```
write tests for the Site model — custom settings and domain matching
```

**Claude:**
```
Here is what I'd cover:

customSettings behaviour:
- Default returns []
- Get with scope returns only that scope's data
- Unknown scope returns []
- Get without scope returns all settings
- setCustomSettings(null) resets to []
- Accepts a serialized string (as the Dao stores it)

Persistence:
- Save and reload via getById() — customSettings still intact
- null persisted and reloaded

Domain matching (via Dao::getByDomain()):
- Exact match on mainDomain
- Exact match on an entry in the domains list
- Wildcard *.example.com matches sub.example.com
- Wildcard does not match the bare parent domain example.com
- Unknown domain returns null

Domain validation:
- Invalid domain name throws InvalidArgumentException
- Wildcard domain *.example.com is accepted

Shall I go ahead?
```

**You:**
```
yes
```

**Claude:**
```
[creates tests/Model/Site/SiteTest.php]
[runs testkit → 14/16 green, 2 failures]

Two issues to fix:

1. testGetByDomainWildcardDoesNotMatchParentDomain
   The site's mainDomain was set to 'example.com', so the Dao's exact-match
   query found the site before the wildcard logic even ran. Fixed by using a
   different mainDomain in that test.

2. testGetByDomainWildcardBackwardCompatDotStar
   setDomains() rejects '.*example.com' as an invalid domain name —
   the .* → * normalisation in the Dao only applies to old data already in
   the database. This path cannot be exercised through the model API. Test removed.

[re-runs testkit → 15/15 green]

All tests pass.
```

---

No context switching, no manual `docker exec`, no copy-pasting output.
Just describe what needs testing and let Claude handle the rest.