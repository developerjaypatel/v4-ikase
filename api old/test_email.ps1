$date = Get-Date -DisplayHint Date
$text = 'Hello World Nick ' + $date
$text | Set-Content 'log_file.txt'

cd\
cd "C:\Program Files\JAM Software\SpamAssassin for Windows"
$command = '.\spamassassin -e ["C:\Users\Nick\Documents\thomas.txt"] "C:\Users\Nick\Documents\nick_output.txt"'
$output = Invoke-Expression $command

cd\
cd "C:\inetpub\wwwroot\ikase.org\api\"
$output | Set-Content 'log_file.txt'

# & "C:\Program Files\JAM Software\SpamAssassin for Windows\spamassassin" -e ["C:\Users\Nick\Documents\thomas.txt"] "C:\Users\Nick\Documents\nick_output.txt"