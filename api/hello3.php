Set IE = CreateObject("InternetExplorer.Application")
IE.visible=true
IE.navigate "https://www.ikase.org/api/eams_carriers_update.php"
while IE.Busy
 WScript.Sleep 10*1000
 wend
IE.Quit

