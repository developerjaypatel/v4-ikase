# Docker DB dumps

There should be two files in this folder:
- `01-ikase.sql`: should contain the database structure, without the `CREATE DATABASE` command.
- `02.data.sql`: should contain all inserts needed to make the system ready for testing around. As this can get quite big, it's being ignored in the repository. You should generate one by yourself - the following command might help: `mysqldump ikase --skip-add-drop-table --add-locks --skip-create-options --result-file=docker/db/dumps/02-data.sql --user=root --host=ikase.website`
