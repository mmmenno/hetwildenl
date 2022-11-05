# App2: Collectieflora & fauna

Voorbeeldje van mogelijkheden die je ten deel vallen als je collectie-items verbindt met Wikidata identifiers van taxons.

## Voorlopig plan
### Vogels en bomen

* lijst van natuurgebieden
* lijst van soorten /vogels/bomen

Natuurgebied ingang, kies gebied
* kaartje van gebied, met topotijdreis achtergrond
* verschillende tijdsblokken of jaren querien bij gbif
* waarnemeningen van vogels en locaties van bomen in dit gebied
* klik op vogel/boom: details van vogel, boom met complete hierarchie en plaatjes uit verschillende bronnen (langzaam uitbreiden!)
* en dan hopelijk via soort naar notable individu
  (denk Domino mus)


## In: Qid natuurgebied

Deze is het ingewikkeldst, maar wel leuk omdat je zo de verbinding met app1 legt. Om van natuurgebied Qid naar een prent met een taxon te komen kan je de volgende stappen doorlopen:

- Haal info over natuurgebied, of preciezer gezegd: probeer de bounds van het gebied te achterhalen.
- Met de bounds kan je de GBIF (of iNaturalist) api aanroepen om waarnemingen binnen het gebied te achterhalen (en op de kaart te tonen?)
- Maak een lijstje van de soorten die waargenomen zijn, eerst met GBIF ids
- Vraag dan aan Wikidata welke Qids bij die GBIF ids horen
- Kijk of er prenten zijn waarop dat taxon Qid wordt afgebeeld
- Toon die prenten (over de kaart, bijvoorbeeld)


## In: Qid taxon