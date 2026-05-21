# Doctrine Schema Filter

OpenDXP registers a DBAL schema filter (`UnmanagedTablesSchemaFilter`) that limits Doctrine's
view of the database to only the tables it manages as ORM entities.

When active, any table not mapped to a Doctrine entity is invisible to schema comparisons.
This prevents `doctrine:schema:update` from generating `DROP TABLE` statements for tables that
OpenDXP or third-party bundles manage outside of the ORM (e.g. via raw SQL or a separate
migration system).

## Default behaviour

The filter is **disabled by default**. It activates automatically for:

- `doctrine:schema:update`
- `doctrine:schema:validate`
- Any console command implementing `ExcludesUnmanagedTablesInterface`

At runtime (outside of these commands) all tables are visible, which is required for features
that inspect the schema directly (e.g. checking whether a table already exists).

## ExcludesUnmanagedTablesInterface

```
OpenDxp\Bundle\CoreBundle\Doctrine\ExcludesUnmanagedTablesInterface
```

Implement this marker interface on a console command that calls `SchemaTool::updateSchema()`
directly. It signals the filter to activate for the duration of that command — the same
protection that `doctrine:schema:update` gets automatically.

Without the filter, `SchemaTool::updateSchema()` compares all database tables against the
provided metadata and generates `DROP TABLE` statements for every table it does not recognise.

### When to use it

Use it whenever a command calls `SchemaTool::updateSchema()` or `SchemaTool::getUpdateSchemaSql()`
with a partial set of metadata (i.e. not all ORM entities, but only those relevant to the command).

### Example

```php
use OpenDxp\Bundle\CoreBundle\Doctrine\ExcludesUnmanagedTablesInterface;
use Symfony\Component\Console\Command\Command;

final class CreateDatabaseTablesCommand extends Command implements ExcludesUnmanagedTablesInterface
{
    // ...
}
```