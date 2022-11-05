# App3: "We are all individuals"

Voorbeeldje van mogelijkheden die je ten deel vallen als je collectie-items verbindt met Wikidata identifiers van taxons.



## In: Qid taxon




## In: Qid individu

Bij een individu kunnen we de volgende info ophalen:

- Wikidata item (met bijv. geboorte- en sterfdatum, plaats)
- eventuele Wikipediapagina's
- afbeelding die op Wikidata bij het item is geplaatst
- Delpher is een mooie bron, maar dat moet wel handmatig
- afbeeldingen waarvan op Commons is vermeld dat ze 'beeldt af' het individu in kwestie


Op de [Commons Query Service](https://commons-query.wikimedia.org/) mag je als ingelogde gebruiker wel, en vanuit een script geen queries draaien. Workaround: zelf query draaien en resultaten als [json](../data/imgs-individuals.json) opslaan.

Deze query haalt alle afbeeldingen van dieren en (heel veel) bomen en waarschijnlijk ook wat andere planten die op Wikidata als individu van een taxon benoemd zijn:

```
select distinct ?item ?itemLabel ?taxon ?taxonLabel ?image with {
  select ?item ?itemLabel ?taxon ?taxonLabel where {
    service <https://query.wikidata.org/sparql> {
      ?item wdt:P10241 ?taxon .
      service wikibase:label { bd:serviceParam wikibase:language "nl,en". 
                              ?item rdfs:label ?itemLabel . 
                              ?taxon rdfs:label ?taxonLabel . 
                             }
    }
  }
} as %wikidataItems where {
  include %wikidataItems .
  ?file wdt:P180 ?item;
        schema:url ?image.
} limit 5000
```

## Individu in werk/collectie

P1441
P195
